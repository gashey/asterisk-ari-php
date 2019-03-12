<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

namespace AriStasisApp\RestClient;


use AriStasisApp\Model\Endpoint;

/**
 * Class Endpoints
 *
 * @package AriStasisApp\RestClient
 */
class Endpoints extends AriRestClient
{
    private const ENDPOINT = 'Endpoint';

    /**
     * List all endpoints.
     *
     * @return Endpoint[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function list(): array
    {
        return $this->getRequest('/endpoints', [], 'array', self::ENDPOINT);
    }

    /**
     * Send a message to some technology URI or endpoint.
     *
     * @param string $to The endpoint resource or technology specific URI to send the message to.
     * Valid resources are sip, pjsip, and xmpp.
     * @param string $from The endpoint resource or technology specific identity to send this message from.
     * Valid resources are sip, pjsip, and xmpp.
     * @param string $body The body of the message.
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function sendMessage(string $to, string $from, string $body = ''): void
    {
        $this->putRequest("/endpoints/sendMessage", ['to' => $to, 'from' => $from, 'body' => $body]);
    }

    /**
     * List available endoints for a given endpoint technology.
     *
     * @param string $tech Technology of the endpoints (sip,iax2,...).
     * @return Endpoint[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function listByTech(string $tech): array
    {
        return $this->getRequest("/endpoints/{$tech}", [], 'array', self::ENDPOINT);
    }

    /**
     * Details for an endpoint.
     *
     * @param string $tech Technology of the endpoint.
     * @param string $resource ID of the endpoint.
     * @return Endpoint|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function get(string $tech, string $resource): Endpoint
    {
        return $this->getRequest("/endpoints/{$tech}/{$resource}", [], 'model', self::ENDPOINT);
    }

    /**
     * Send a message to some endpoint in a technology.
     *
     * @param string $tech Technology of the endpoint.
     * @param string $resource ID of the endpoint.
     * @param string $from The endpoint resource or technology specific identity to send this message from.
     * Valid resources are sip, pjsip, and xmpp.
     * @param string $body The body of the message.
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function sendMessageToEndpoint(string $tech, string $resource, string $from, string $body = ''): void
    {
        $this->putRequest("/endpoints/{$tech}/{$resource}/sendMessage", ['from' => $from, 'body' => $body]);
    }
}