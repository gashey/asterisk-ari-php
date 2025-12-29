<?php

declare(strict_types=1);

namespace OpiyOrg\AriClient\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use OpiyOrg\AriClient\AriServiceProvider;
use OpiyOrg\AriClient\Client\Rest\Settings as RestSettings;
use OpiyOrg\AriClient\Client\WebSocket\Settings as WebSocketSettings;
use OpiyOrg\AriClient\Client\WebSocket\WebSocketClientInterface;
use OpiyOrg\AriClient\StasisApplicationInterface;
use PHPUnit\Framework\TestCase;

class AriServiceProviderTest extends TestCase
{
    private Container $app;
    private AriServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Container();
        $this->app->singleton('config', function () {
            return new Repository([
                'ari' => [
                    'host' => 'localhost',
                    'port' => 8088,
                    'user' => 'asterisk',
                    'password' => 'asterisk',
                    'root_uri' => '/ari',
                    'wss_enabled' => false,
                    'subscribe_all' => false,
                ]
            ]);
        });
        $this->provider = new AriServiceProvider($this->app);
    }

    public function testRegister(): void
    {
        $this->provider->register();

        $this->assertInstanceOf(RestSettings::class, $this->app->make(RestSettings::class));
        $this->assertInstanceOf(WebSocketSettings::class, $this->app->make(WebSocketSettings::class));
    }

    public function testBindWebSocketClient(): void
    {
        $this->provider->register();

        $stasisApp = $this->createMock(StasisApplicationInterface::class);

        $client = $this->app->make(WebSocketClientInterface::class, [
            'stasisApplication' => $stasisApp
        ]);

        $this->assertInstanceOf(WebSocketClientInterface::class, $client);
    }
}
