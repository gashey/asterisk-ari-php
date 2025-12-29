<?php

/** @copyright 2020 ng-voice GmbH */

declare(strict_types=1);

namespace OpiyOrg\AriClient\Client\WebSocket\Swoole;

use OpiyOrg\AriClient\Client\WebSocket\{AbstractWebSocketClient, Settings as WebSocketClientSettings};
use OpiyOrg\AriClient\StasisApplicationInterface;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

/**
 * A Swoole ARI web socket client implementation.
 *
 * @package OpiyOrg\AriClient\Client\WebSocket\Swoole
 *
 * @author Lukas Stermann <lukas@ng-voice.com>
 * @internal
 *
 */
class WebSocketClient extends AbstractWebSocketClient
{
    private ?Settings $optionalSettings;

    /**
     * WebSocket constructor.
     *
     * @param WebSocketClientSettings $webSocketClientSettings The settings
     * for this web socket client
     * @param StasisApplicationInterface $stasisApplication The web socket client
     * @param Settings|null $optionalSettings Optional settings for
     * this web socket client
     */
    public function __construct(
        WebSocketClientSettings $webSocketClientSettings,
        StasisApplicationInterface $stasisApplication,
        ?Settings $optionalSettings = null
    ) {
        parent::__construct($webSocketClientSettings, $stasisApplication);
        $this->optionalSettings = $optionalSettings;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        run(function () {
            $uri = $this->createUri($this->stasisApplication);
            $parsedUri = parse_url($uri);

            $host = $parsedUri['host'];
            $port = (int) ($parsedUri['port'] ?? ($parsedUri['scheme'] === 'wss' ? 443 : 80));
            $ssl = $parsedUri['scheme'] === 'wss';

            $client = new Client($host, $port, $ssl);

            if ($this->optionalSettings !== null) {
                $client->set($this->optionalSettings->getOptions());
            }

            $path = ($parsedUri['path'] ?? '') . '?' . ($parsedUri['query'] ?? '');

            if (!$client->upgrade($path)) {
                $this->logger->error(
                    sprintf(
                        "ARI Connection error | Code -> '%s' | Message -> '%s'",
                        $client->errCode,
                        swoole_strerror($client->errCode)
                    ),
                    [__FUNCTION__]
                );
                return;
            }

            $this->logger->info(
                'Successfully connected to Asterisk web socket server',
                [__FUNCTION__]
            );

            $this->onConnectionHandlerLogic();

            while (true) {
                $frame = $client->recv();
                if ($frame === false || $frame === null) {
                    // Check for timeout error code (60 on macOS, 110 on Linux)
                    if ($client->errCode === 60 || $client->errCode === 110) {
                        continue;
                    }

                    $this->logger->error(
                        sprintf(
                            "ARI Connection closed unexpectedly | Code -> '%s' | Message -> '%s'",
                            $client->errCode,
                            swoole_strerror($client->errCode)
                        ),
                        [__FUNCTION__]
                    );
                    break;
                }

                if ($frame->data === '') {
                    continue;
                }

                if ($this->isInDebugMode) {
                    $this->logger->debug(
                        sprintf("Asterisk web socket server sent raw message: '%s'", $frame->data),
                        [__FUNCTION__]
                    );
                }

                $this->onMessageHandlerLogic($frame->data);
            }
        });
    }
}
