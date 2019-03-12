<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

namespace AriStasisApp\WebSocketClient;


use AriStasisApp\BasicStasisApp;
use GuzzleHttp\Exception\GuzzleException;
use JsonMapper;
use JsonMapper_Exception;
use Monolog\Logger;
use Nekland\Woketo\Core\AbstractConnection;
use Nekland\Woketo\Exception\WebsocketException;
use Nekland\Woketo\Message\TextMessageHandler;
use function AriStasisApp\{getShortClassName, initLogger};

/**
 * Class LocalAppMessageHandler
 *
 */
class LocalAppMessageHandler extends TextMessageHandler
{
    /**
     * @var BasicStasisApp
     */
    private $myApp;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var JsonMapper
     */
    private $jsonMapper;

    /**
     * RemoteAppMessageHandler constructor.
     * @param BasicStasisApp $myApp
     * @param JsonMapper|null $jsonMapper
     */
    function __construct(BasicStasisApp $myApp, JsonMapper $jsonMapper = null)
    {
        $this->logger = initLogger(getShortClassName($this));
        $this->myApp = $myApp;

        if (is_null($jsonMapper)) {
            $this->jsonMapper = new JsonMapper();
        } else {
            $this->jsonMapper = $jsonMapper;
        }
    }

    /**
     * @inheritdoc
     *
     * On connection to the web socket, we tell Asterisk only to send Message we are
     * actually handling in our application. This will increase performance.
     */
    public function onConnection(AbstractConnection $connection): void
    {
        $this->logger->info('Connection to asterisk successful...');

        $allowedMessages = array_diff(get_class_methods($this->myApp), get_class_methods(BasicStasisApp::class));
        // Reindex the allowedMessages array so we can loop through it
        $allowedMessages = array_values($allowedMessages);

        for ($i = 0; $i < sizeof($allowedMessages); $i++) {
            $allowedMessages[$i] = ucfirst($allowedMessages[$i]);
        }

        try {
            $applicationsClient = $this->myApp->getApplicationsClient();
            $applicationsClient->filter(getShortClassName($this->myApp), $allowedMessages);
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage(), [__FUNCTION__]);
        }

        $this->logger->debug("Set message filter in Asterisk.", [__FUNCTION__]);
        $this->logger->info("Waiting for Message.");
    }

    /**
     * @inheritdoc
     *
     * Every incoming message from Asterisk will be handled within
     * the provided BasicStasisApp classes function
     */
    public function onMessage(string $data, AbstractConnection $connection): void
    {
        $this->logger->debug("Received raw message from asterisk WebSocket server: {$data}", [__FUNCTION__]);
        $decodedJson = json_decode($data);
        $ariEventType = $decodedJson->type;
        $eventPath = "AriStasisApp\\Model\\Message\\" . $ariEventType;

        try {
            $mappedEventObject = $this->jsonMapper->map($decodedJson, new $eventPath);
            $functionName = lcfirst($ariEventType);
            $this->myApp->$functionName($mappedEventObject);
            $this->logger->debug("Message successfully handled by your app: {$data}", [__FUNCTION__]);
        } catch (JsonMapper_Exception $jsonMapper_Exception) {
            $this->logger->error($jsonMapper_Exception->getMessage(), [__FUNCTION__]);
        }
    }

    /**
     * @inheritdoc
     */
    public function onDisconnect(AbstractConnection $connection): void
    {
        $this->logger->warning('Connection to Asterisk was closed.', [__FUNCTION__]);
    }

    /**
     * @inheritdoc
     * @throws WebsocketException
     */
    public function onError(WebsocketException $websocketException, AbstractConnection $connection): void
    {
        $this->logger->error($websocketException->getMessage(), [__FUNCTION__]);
        throw $websocketException;
    }
}