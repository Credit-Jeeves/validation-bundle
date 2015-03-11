<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use JMS\Serializer\DeserializationContext;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use Guzzle\Http\Client as HttpClient;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use Monolog\Logger;

class MRIClient implements ClientInterface
{
    use Debug;
    use Settings;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ExceptionCatcher $exceptionCatcher
     * @param Serializer $serializer
     * @param Logger $logger
     */
    public function __construct(
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        Logger $logger
    ) {
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->httpClient = new HttpClient();
        $this->logger = $logger;
    }

    /**
     * @TODO When will be solved problem with soap builder by besimple bundle it's must be removed
     */
    public function build()
    {
    }

    /**
     * @param $method
     * @param array $params
     * @return MRIResponse
     */
    public function sendRequest($method, array $params)
    {
        try {
            $baseParams = [
                '$api'    => $method,
                '$format' => 'json'
            ];
            /** @var MRISettings $mriSettings */
            $mriSettings = $this->getSettings();
            $authorization = $mriSettings->getParameters()['authorization'];
            $GETParameters = array_merge($baseParams, $params);
            $headers = [
                'Authorization' => sprintf('Basic %s', $authorization),
                'Accept'        => 'application/json',
            ];

            $uri = sprintf(
                '%s?%s',
                $mriSettings->getUrl(),
                http_build_query($GETParameters)
            );

            $this->debugMessage(sprintf("Request to uri %s", $uri));
            $request = $this->httpClient->get($uri, $headers);

            return $this->manageResponse($this->httpClient->send($request));
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

        return false;
    }

    /**
     * @param $response
     * @return MRIResponse
     */
    protected function manageResponse($response)
    {
        $httpCode = $response->getStatusCode();
        $body = $response->getBody();
        $this->debugMessage(sprintf('Http code: %s', $httpCode));
        $this->debugMessage(sprintf('Body: %s', $body));

        if ($httpCode !== 200) {
            throw new Exception(sprintf("Bad http code(%s) from MRI", $httpCode));
        }

        $context = new DeserializationContext();
        $context->setGroups(['MRI']);

        $mriResponse = $this->serializer->deserialize(
            $body->__toString(),
            'RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse',
            'json',
            $context
        );

        return $mriResponse;
    }

    /**
     * @param $externalPropertyId
     * @return MRIResponse
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';

        $params = [
            'RMPROPID' => $externalPropertyId
        ];

        $this->debugMessage("Call MRI method: {$method}");
        $response = $this->sendRequest($method, $params);

        return $response;
    }

    /**
     * Cap, will be developed in the future
     * Don't use it
     */
    public function getPaymentDetails($externalPropertyId)
    {
        $method = 'MRI_S-PMRM_PaymentDetailsByPropertyID';

        $params = [
            'RMPROPID' => $externalPropertyId
        ];

        $this->debugMessage("Call MRI method: {$method}");
        $response = $this->sendRequest($method, $params);

        return $response;
    }
}
