<?php

declare(strict_types=1);

namespace OpiyOrg\AriClient;

use Illuminate\Support\ServiceProvider;
use OpiyOrg\AriClient\Client\Rest\Settings as RestSettings;
use OpiyOrg\AriClient\Client\WebSocket\Settings as WebSocketSettings;
use OpiyOrg\AriClient\Client\WebSocket\Factory as WebSocketFactory;
use OpiyOrg\AriClient\Client\WebSocket\WebSocketClientInterface;

class AriServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ari.php', 'ari');

        $this->app->singleton(RestSettings::class, function ($app) {
            $config = $app['config']['ari'];
            return new RestSettings(
                $config['user'],
                $config['password'],
                $config['host'],
                $config['port'],
                $config['root_uri']
            );
        });

        $this->app->singleton(WebSocketSettings::class, function ($app) {
            $config = $app['config']['ari'];
            $settings = new WebSocketSettings(
                $config['user'],
                $config['password'],
                $config['host'],
                $config['port'],
                $config['root_uri']
            );

            if ($config['wss_enabled']) {
                $settings->setWssEnabled(true);
            }

            if ($config['subscribe_all']) {
                $settings->setIsSubscribeAll(true);
            }

            return $settings;
        });

        $this->app->bind(WebSocketClientInterface::class, function ($app, $parameters) {
            $stasisApp = $parameters['stasisApplication'];
            return WebSocketFactory::create(
                $app->make(WebSocketSettings::class),
                $stasisApp
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ari.php' => config_path('ari.php'),
            ], 'ari-config');
        }
    }
}
