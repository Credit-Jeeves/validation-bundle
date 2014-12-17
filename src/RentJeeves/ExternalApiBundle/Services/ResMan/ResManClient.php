<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use Guzzle\Http\Client as HttpClient;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;

class ResManClient implements ClientInterface
{
    use Debug;
    use Settings;

    /**
     * @var ResManClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $integrationPartnerId;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    public function __construct(
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        $integrationPartnerId,
        $apiKey,
        $apiUrl
    ) {
        $this->exceptionCatcher = $exceptionCatcher;
        $this->integrationPartnerId = $integrationPartnerId;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->serializer = $serializer;
    }

    public function build()
    {
        $this->httpClient = new HttpClient();
    }

    public function sendRequest($method, array $params)
    {
        try {
            $baseParams = array(
                'IntegrationPartnerID'  => $this->integrationPartnerId,
                'ApiKey'                => $this->apiKey,
            );

            $uri = $this->apiUrl . $method;

            $postBody = array_merge($baseParams, $params, $this->settings->getParameters());

            $response = $this->httpClient->post($uri, $headers = null, http_build_query($postBody));

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            //@TODO make serializer and return object and check errors
        } catch (Exception $e) {
            $this->debugMessage(
                sprintf(
                    "Error message: %s In file: %s By line: %s",
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );

            $this->exceptionCatcher->handleException($e);
        }
    }

    public function getResidentTransactions()
    {
    }
}
