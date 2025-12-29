<?php

/** @copyright 2020 ng-voice GmbH */

declare(strict_types=1);

namespace OpiyOrg\AriClient\Client\WebSocket;

use OpiyOrg\AriClient\Client\WebSocket\Swoole\{
    Settings as OptionalSwooleSettings,
    WebSocketClient as SwooleWebSocketClient
};
use OpiyOrg\AriClient\StasisApplicationInterface;

/**
 * Factory class to create new instances of an ARI web socket client
 * depending on the implementation preferred.
 *
 * @package OpiyOrg\AriClient\Client\WebSocket
 *
 * @author Lukas Stermann <lukas@ng-voice.com>
 */
class Factory
{
    /**
     * Create a new web socket client.
     *
     * @param Settings $ariWebSocketClientSettings The
     * web socket client settings
     * @param StasisApplicationInterface $myStasisApp Your Stasis app
     * that shall register within the connected Asterisk instance
     *
     * @return WebSocketClientInterface The currently recommended
     * web socket client implementation.
     */
    public static function create(
        Settings $ariWebSocketClientSettings,
        StasisApplicationInterface $myStasisApp
    ): WebSocketClientInterface {
        return self::createSwoole($ariWebSocketClientSettings, $myStasisApp);
    }

    /**
     * Create an instance of the Swoole web socket client implementation.
     *
     * @param Settings $ariWebSocketClientSettings The
     * web socket client settings
     * @param StasisApplicationInterface $myStasisApp Your Stasis app
     * that shall register within the connected Asterisk instance
     * @param OptionalSwooleSettings|null $optionalSwooleSettings Optional
     * settings for the specific Swoole implementation of the web socket
     * client
     *
     * @return SwooleWebSocketClient The swoole web socket client
     * implementation.
     */
    public static function createSwoole(
        Settings $ariWebSocketClientSettings,
        StasisApplicationInterface $myStasisApp,
        ?OptionalSwooleSettings $optionalSwooleSettings = null
    ): SwooleWebSocketClient {
        return new SwooleWebSocketClient(
            $ariWebSocketClientSettings,
            $myStasisApp,
            $optionalSwooleSettings
        );
    }
}
