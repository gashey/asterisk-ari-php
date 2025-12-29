<?php

declare(strict_types=1);

namespace OpiyOrg\AriClient;

use Illuminate\Support\ServiceProvider;
use OpiyOrg\AriClient\Client\Rest\Settings as RestSettings;
use OpiyOrg\AriClient\Client\WebSocket\Settings as WebSocketSettings;
use OpiyOrg\AriClient\Client\WebSocket\Factory as WebSocketFactory;
use OpiyOrg\AriClient\Client\WebSocket\WebSocketClientInterface;
use OpiyOrg\AriClient\Client\WebSocket\Swoole\Settings as SwooleSettings;

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
            $config = $app['config']['ari'];

            $swooleSettings = null;
            if (isset($config['swoole']) && is_array($config['swoole'])) {
                $swooleSettings = new SwooleSettings();
                $swooleSettings->setOptions($config['swoole']);
            }

            return WebSocketFactory::createSwoole(
                $app->make(WebSocketSettings::class),
                $stasisApp,
                $swooleSettings
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
