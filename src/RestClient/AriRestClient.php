<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

namespace AriStasisApp\RestClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use JsonMapper;
use JsonMapper_Exception;
use Monolog\Logger;
use function AriStasisApp\{getShortClassName, initLogger, parseAriSettings};

/**
 * Class AriRestClient
 *
 * @package AriStasisApp\RestClient
 */
class AriRestClient
{
    protected const MODEL = 'model';
    protected const ARRAY = 'array';
    private const QUERY = 'query';

    /**
     * @var JsonMapper
     */
    protected $jsonMapper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var string
     */
    private $rootUri;

    /**
     * AriRestClient constructor.
     *
     * @param string $ariUser
     * @param string $ariPassword
     * @param array $otherAriSettings
     * @param Client|null $guzzleClient
     */
    function __construct(
        string $ariUser,
        string $ariPassword,
        array $otherAriSettings = [],
        Client $guzzleClient = null
    )
    {
        $ariSettings = parseAriSettings($otherAriSettings);
        $this->logger = initLogger(getShortClassName($this));

        $httpType = $ariSettings['httpsEnabled'] ? 'https' : 'http';
        $baseUri = "{$httpType}://{$ariSettings['host']}:{$ariSettings['port']}/";
        $this->rootUri = $ariSettings['rootUri'];

        if ($guzzleClient === null) {
            $this->guzzleClient =
                new Client(['base_uri' => $baseUri, 'auth' => [$ariUser, $ariPassword]]);
        } else {
            $this->guzzleClient = $guzzleClient;
        }

        $this->jsonMapper = new JsonMapper();
        // Allow mapping to private and protected properties and setter methods
        $this->jsonMapper->bIgnoreVisibility = true;
        $this->jsonMapper->bExceptionOnUndefinedProperty = true;
    }

    /**
     * Sends a GET request via a GuzzleClient to Asterisk.
     *
     * @param string $uri
     * @param array $queryParameters
     * @param string $returnType
     * @param string $returnModelClassName
     * @return array|Response|object
     * @throws GuzzleException
     */
    protected function getRequest(
        string $uri,
        array $queryParameters = [],
        string $returnType = '',
        string $returnModelClassName = ''
    ) {
        $method = 'GET';
        $uri = $this->rootUri . $uri;
        $this->logRequest($method, $uri, $queryParameters);
        $response = $this->guzzleClient->request($method, $uri, [self::QUERY => $queryParameters]);
        $this->logResponse($response, $method, $uri);
        return $this->formatResponse($response, $returnType, $returnModelClassName);
    }

    /**
     * Logs Requests for debugging purposes.
     *
     * @param string $method
     * @param string $uri
     * @param array $queryParameters
     * @param array $body
     */
    private function logRequest(string $method, string $uri, array $queryParameters = [], array $body = [])
    {
        $queryParameters = print_r($queryParameters, true);
        $body = print_r($body, true);
        $this->logger->debug("Sending Request... Method: {$method} | URI: {$uri} | "
            . "QueryParameters: {$queryParameters} | Body: {$body}"
        );
    }

    /**
     * Logs Responses for debugging purposes.
     *
     * @param Response $response
     * @param string $method
     * @param string $uri
     */
    private function logResponse(Response $response, string $method, string $uri)
    {
        $this->logger->debug("Received Response... Method: {$method} | URI: {$uri} | "
            . "ResponseCode: {$response->getStatusCode()} | Reason: {$response->getReasonPhrase()} | "
            . "Body: {$response->getBody()}"
        );
    }

    /**
     * @param Response $response
     * @param string $returnType
     * @param string $returnModelClassName
     * @return array|Response|object
     */
    private function formatResponse(Response $response, string $returnType, string $returnModelClassName)
    {
        if (($returnType !== '') && ($returnModelClassName !== '')) {
            $modelClassName = "AriStasisApp\\Model\\" . $returnModelClassName;
            if ($returnType === 'array') {
                return $this->mapJsonArrayToAriObjects($response, $modelClassName);
            } elseif ($returnType === 'model') {
                return $this->mapJsonToAriObject($response, $modelClassName);
            }
        }
        return $response;
    }

    /**
     * @param Response $response
     * @param string $targetObjectType
     * @return array
     */
    private function mapJsonArrayToAriObjects(Response $response, string $targetObjectType): array
    {
        $decodedBody = json_decode($response->getBody());
        try {
            $mappedElements = [];
            for ($i = 0; $i < sizeof($decodedBody); $i++) {
                $mappedElements[$i] = $this->jsonMapper->map($decodedBody[$i], new $targetObjectType);
            }
            return $mappedElements;
        } catch (JsonMapper_Exception $jsonMapper_Exception) {
            $this->logger->error($jsonMapper_Exception->getMessage());
            exit(1);

        }
    }

    /**
     * @param Response $response
     * @param string $targetObjectType
     * @return object
     */
    private function mapJsonToAriObject(Response $response, string $targetObjectType)
    {
        try {
            return $this->jsonMapper->map(json_decode($response->getBody()), new $targetObjectType);
        } catch (JsonMapper_Exception $jsonMapper_Exception) {
            $this->logger->error($jsonMapper_Exception->getMessage());
            exit(1);
        }
    }

    /**
     * Sends a POST request via a GuzzleClient to Asterisk.
     *
     * @param string $uri
     * @param array $queryParameters
     * @param array $body
     * @param string $returnType
     * @param string $returnModelClassName
     * @return array|Response|object
     * @throws GuzzleException
     */
    protected function postRequest(
        string $uri,
        array $queryParameters = [],
        array $body = [],
        string $returnType = '',
        string $returnModelClassName = ''
    ) {
        $method = 'POST';
        $uri = $this->rootUri . $uri;
        $this->logRequest($method, $uri, $queryParameters, $body);
        $response = $this->guzzleClient->request($method, $uri, ['json' => $body, self::QUERY => $queryParameters]);
        $this->logResponse($response, $method, $uri);
        return $this->formatResponse($response, $returnType, $returnModelClassName);
    }

    /**
     * Sends a PUT request via a GuzzleClient to Asterisk.
     *
     * @param string $uri
     * @param array $body
     * @param string $returnType
     * @param string $returnModelClassName
     * @return array|Response|object
     * @throws GuzzleException
     */
    protected function putRequest(
        string $uri,
        array $body = [],
        string $returnType = '',
        string $returnModelClassName = ''
    ) {
        $method = 'PUT';
        $uri = $this->rootUri . $uri;
        $this->logRequest($method, $uri, [], $body);
        $response = $this->guzzleClient->request($method, $uri, ['json' => $body]);
        $this->logResponse($response, $method, $uri);
        return $this->formatResponse($response, $returnType, $returnModelClassName);
    }

    /**
     * Sends a DELETE request via a GuzzleClient to Asterisk.
     *
     * @param string $uri
     * @param array $queryParameters
     * @param string $returnType
     * @param string $returnModelClassName
     * @return array|Response|object
     * @throws GuzzleException
     */
    protected function deleteRequest(
        string $uri,
        array $queryParameters = [],
        string $returnType = '',
        string $returnModelClassName = ''
    ) {
        $method = 'DELETE';
        $uri = $this->rootUri . $uri;
        $this->logRequest($method, $uri, $queryParameters);
        $response = $this->guzzleClient->request($method, $uri, [self::QUERY => $queryParameters]);
        $this->logResponse($response, $method, $uri);
        return $this->formatResponse($response, $returnType, $returnModelClassName);
    }
}