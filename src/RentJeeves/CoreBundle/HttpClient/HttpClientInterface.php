<?php

namespace RentJeeves\CoreBundle\HttpClient;

use Guzzle\Http\Message\Response;

interface HttpClientInterface
{
    /**
     * @param $method
     * @param $uri
     * @param array $headers
     * @param mixed $body
     * @param array $options
     * @return Response
     */
    public function send($method, $uri, $headers = null, $body = null, array $options = []);

    /**
     * Set the base URL of the client
     *
     * @param string $url The base service endpoint URL of the webservice
     *
     * @return HttpClientInterface
     */
    public function setBaseUrl($url);

    /**
     * Set config for client
     * @param array $options
     * @return HttpClientInterface
     */
    public function setConfig(array $options);

    /**
     * @param int $retries
     * @return HttpClientInterface
     */
    public function setNumberRetries($retries);
}
