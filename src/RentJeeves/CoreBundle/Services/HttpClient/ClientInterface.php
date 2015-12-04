<?php

namespace RentJeeves\CoreBundle\Services\HttpClient;

use Guzzle\Http\Message\Response;

interface ClientInterface
{
    /**
     * @param $method
     * @param $uri
     * @param null $headers
     * @param null $body
     * @param array $options
     * @return Response
     */
    public function send($method, $uri, $headers = null, $body = null, array $options = []);

    /**
     * Set the base URL of the client
     *
     * @param string $url The base service endpoint URL of the webservice
     *
     * @return ClientInterface
     */
    public function setBaseUrl($url);

    /**
     * Set config for client
     * @param array $options
     * @return ClientInterface
     */
    public function setConfig(array $options);

    /**
     * @param int $retries
     * @return ClientInterface
     */
    public function setNumberRetries($retries);
}
