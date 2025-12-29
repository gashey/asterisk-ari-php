<?php

declare(strict_types=1);

namespace OpiyOrg\AriClient\Tests\Client\WebSocket\Swoole;

use OpiyOrg\AriClient\Client\WebSocket\Swoole\Settings as SwooleSettings;
use OpiyOrg\AriClient\Client\WebSocket\Swoole\WebSocketClient;
use OpiyOrg\AriClient\Client\WebSocket\Settings;
use OpiyOrg\AriClient\StasisApplicationInterface;
use PHPUnit\Framework\TestCase;

class WebSocketClientTest extends TestCase
{
    public function testConstruct(): void
    {
        $stasisApp = $this->createMock(StasisApplicationInterface::class);
        $webSocketSettings = new Settings('asterisk', 'asterisk');
        $optionalSettings = new SwooleSettings();

        $client = new WebSocketClient(
            $webSocketSettings,
            $stasisApp,
            $optionalSettings
        );

        $this->assertInstanceOf(WebSocketClient::class, $client);
    }
}
