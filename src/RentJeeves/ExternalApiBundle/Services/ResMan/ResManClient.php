<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResMan;
use RentJeeves\ExternalApiBundle\Model\ResMan\Response;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use Guzzle\Http\Client as HttpClient;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use JMS\Serializer\DeserializationContext;

class ResManClient implements ClientInterface
{
    use Debug;
    use Settings;

    const BASE_RESPONSE = 'baseResponse';

    protected $mappingResponse = array(
        self::BASE_RESPONSE          => 'RentJeeves\ExternalApiBundle\Model\ResMan\ResMan',
        'GetResidentTransactions2_0' => 'RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions',
    );

    /**
     * @var HttpClient
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
        $this->httpClient = new HttpClient();
    }

    /**
     * @TODO When will be solved problem with soap builder by besimple bundle it's must be removed
     */
    public function build()
    {
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
            $request = $this->httpClient->post($uri, $headers = null, $postBody);
            $response = $this->httpClient->send($request);
            $httpCode = $response->getStatusCode();
            $body = $response->getBody();
            $this->debugMessage(
                sprintf(
                    'Http code: %s',
                    $httpCode
                )
            );
            $this->debugMessage(
                sprintf(
                    'Body: %s',
                    $body
                )
            );
            /**
             * ResMan return bad xml, that's why we need two times deserialize
             * 1) We deserialize base response
             * 2) We deserialize xml from one of the field response
             * @var $resMan ResMan
             */
            $resMan = $this->deserializeResponse($body, $this->mappingResponse[self::BASE_RESPONSE]);
            if (!($resMan instanceof ResMan) || $resMan->getStatus() !== 'Success') {
                throw new Exception(
                    sprintf(
                        "Can't deserialize response. Http code: %s. Body: %s",
                        $httpCode,
                        $body
                    )
                );
            }
            $response = $resMan->getResponse();
            $response = str_replace(
                ['&lt;', '&gt;', 'http://my-company.com/namespace'],
                ['<', '>', 'http://www.w3.org/2005/Atom'],
                $response
            );

            return $this->deserializeResponse($response, $this->mappingResponse[$method]);
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

    /**
     * @param $externalPropertyId
     *
     * @return ResidentTransactions
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $method = 'GetResidentTransactions2_0';
        $params = array(
            'PropertyID' => $externalPropertyId
        );

        return $this->sendRequest($method, $params);
    }

    protected function deserializeResponse($data, $class)
    {
        $context = new DeserializationContext();
        $context->setGroups(array('ResMan'));

        return $this->serializer->deserialize($data, $class, 'xml', $context);
    }
}
