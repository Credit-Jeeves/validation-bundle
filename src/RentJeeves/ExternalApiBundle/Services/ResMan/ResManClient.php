<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResMan;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use Guzzle\Http\Client as HttpClient;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use JMS\Serializer\DeserializationContext;
use DateTime;
use Monolog\Logger;

/**
 * @method ResManSettings getSettings
 */
class ResManClient implements ClientInterface
{
    use Debug;
    use Settings;

    const BASE_RESPONSE = 'baseResponse';

    const DEFAULT_DESCRIPTION = 'Send Request "%s" for account "%s"';

    protected $mappingResponse = [
        self::BASE_RESPONSE          => 'RentJeeves\ExternalApiBundle\Model\ResMan\ResMan',
        'GetResidentTransactions2_0' => 'RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions',
    ];

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

    /**
     * @var array
     */
    protected $groupDeserialize = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ExceptionCatcher $exceptionCatcher
     * @param Serializer $serializer
     * @param Logger $logger
     * @param string $integrationPartnerId
     * @param string $apiKey
     * @param string $apiUrl
     */
    public function __construct(
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        Logger $logger,
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
        $this->logger = $logger;
    }

    protected function setDefaultGroupDeserialize()
    {
        $this->groupDeserialize = ['ResMan'];
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
     * @param bool $shouldBeSerializedTwice
     * @return bool|mixed
     */
    public function sendRequest($method, array $params, $shouldBeSerializedTwice = true)
    {
        try {
            $baseParams = [
                'IntegrationPartnerID'  => $this->integrationPartnerId,
                'ApiKey'                => $this->apiKey,
            ];

            $uri = $this->apiUrl . $method;

            $postBody = array_merge($baseParams, $params, $this->settings->getParameters());
            $this->logger->debug(sprintf("Send request to resman with parameters:%s", print_r($postBody, true)));
            $request = $this->httpClient->post($uri, $headers = null, $postBody);

            return $this->manageResponse($this->httpClient->send($request), $method, $shouldBeSerializedTwice);
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
     * @param $method
     * @param $shouldBeSerializedTwice
     * @return mixed
     * @throws Exception
     */
    protected function manageResponse($response, $method, $shouldBeSerializedTwice)
    {
        $httpCode = $response->getStatusCode();
        $body = $response->getBody();
        $this->debugMessage(sprintf('Http code: %s', $httpCode));
        $this->debugMessage(sprintf('Body: %s', $body));
        /**
         * ResMan return bad xml, that's why we need two times deserialize
         * 1) We deserialize base response
         * 2) We deserialize xml from one of the field response
         * @var $resMan ResMan
         */
        $resMan = $this->deserializeResponse($body, $this->mappingResponse[self::BASE_RESPONSE]);
        if (!($resMan instanceof ResMan)) {
            $message = sprintf("Can't deserialize response. Http code: %s. Body: %s", $httpCode, $body);
            $this->debugMessage($message);
            throw new Exception($message);
        }

        if ($resMan->getStatus() !== 'Success') {
            $message = sprintf("Failed request. Http code: %s. Body: %s", $httpCode, $body);
            $this->debugMessage($message);
            throw new Exception($message);
        }

        if ($shouldBeSerializedTwice === false) {
            return $resMan;
        }

        $response = $resMan->getResponseString();

        /**
         * @TODO
         * Serializer not support namespaces for @XmlList
         * Currently it's in developing process
         * https://github.com/schmittjoh/serializer/pull/301
         * After it's will be finished, we must refactoring code and remove
         * replace for Customer
         */
        $response = str_replace(
            ['&lt;', '&gt;', 'http://my-company.com/namespace', 'MITS:Customer'],
            ['<', '>', 'http://www.w3.org/2005/Atom', 'Customer'],
            $response
        );

        return $this->deserializeResponse($response, $this->mappingResponse[$method]);
    }

    /**
     * @param $data
     * @param $class
     * @return object
     */
    protected function deserializeResponse($data, $class)
    {
        $context = new DeserializationContext();
        if (!empty($this->groupDeserialize)) {
            $context->setGroups($this->groupDeserialize);
        } else {
            $this->setDefaultGroupDeserialize();
            $context->setGroups($this->groupDeserialize);
        }

        return $this->serializer->deserialize($data, $class, 'xml', $context);
    }

    /**
     * @param $externalPropertyId
     *
     * @return ResidentTransactions
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $method = 'GetResidentTransactions2_0';
        $params = [
            'PropertyID' => $externalPropertyId
        ];

        $this->debugMessage("Call ResMan method: {$method}");

        return $this->sendRequest($method, $params);
    }

    /**
     * @param $externalPropertyId
     * @param DateTime $batchDate
     * @param string $description
     * @param mixed $accountId Can be get from settings
     * @return mixed
     */
    public function openBatch($externalPropertyId, DateTime $batchDate, $description = null, $accountId = null)
    {
        $method = 'OpenBatch';
        $this->debugMessage("Call ResMan method: {$method}");
        $accountId = $accountId ?: $this->getSettings()->getAccountId();
        $params = [
            'AccountID' => strtolower($accountId),
            'PropertyID' => strtolower($externalPropertyId),
            'Description' => $description ?:  sprintf(self::DEFAULT_DESCRIPTION, $method, $accountId),
            'Date' => $batchDate->format('Y-m-d')
        ];

        $this->groupDeserialize = ['ResManOpenBatch'];
        /** @var ResMan $response */
        $response = $this->sendRequest($method, $params, false);
        $this->setDefaultGroupDeserialize();

        if ($response && $response->getResponse()) {
            return $response->getResponse()->getBatchId();
        }

        return null;
    }

    /**
     * @param $externalPropertyId
     * @param $accountingBatchId
     * @param mixed $accountId Can be get from settings
     * @return bool
     */
    public function closeBatch($externalPropertyId, $accountingBatchId, $accountId = null)
    {
        $method = 'CloseBatch';
        $this->debugMessage("Call ResMan method: {$method}");
        $accountId = $accountId ?: $this->getSettings()->getAccountId();
        $params = [
            'AccountID' => strtolower($accountId),
            'PropertyID' => strtolower($externalPropertyId),
            'BatchID' => strtolower($accountingBatchId)
        ];

        /** @var ResMan $response */
        return !!$this->sendRequest($method, $params, false);
    }

    /**
     * @param $residentTransactionsXml
     * @param string $externalPropertyId
     * @param null $accountId
     * @return bool
     */
    public function addPaymentToBatch(
        $residentTransactionsXml,
        $externalPropertyId,
        $accountId = null
    ) {
        $method = 'AddPaymentToBatch';
        $this->debugMessage("Call ResMan method: {$method}");
        $accountId = $accountId ?: $this->getSettings()->getAccountId();
        $params = [
            'AccountID'  => strtolower($accountId),
            'PropertyID' => strtolower($externalPropertyId),
            'xml'        => $residentTransactionsXml,
        ];

        $result = $this->sendRequest($method, $params, false);

        return ($result instanceof ResMan)? true : false;
    }
}
