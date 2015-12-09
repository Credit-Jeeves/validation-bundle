<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Order;
use Guzzle\Http\Message\Response;
use JMS\Serializer\DeserializationContext;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Model\MRI\MRIOrder;
use RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse;
use RentJeeves\ExternalApiBundle\Model\MRI\Payment;
use RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use RentJeeves\CoreBundle\HttpClient\HttpClientInterface as HttpClient;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use Monolog\Logger;

class MRIClient implements ClientInterface
{
    use Debug;
    use Settings;

    const LOG_PREFIX = '[MRI Client]';

    const OPERATION_TIMEOUT = 180;

    const MAX_NUMBER_RETRIES = 3;

    protected $mappingSerialize = [
        'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID' => 'RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse',
        'MRI_S-PMRM_PaymentDetailsByPropertyID' => 'RentJeeves\ExternalApiBundle\Model\MRI\Payment',
        'MRI_S-PMRM_ResidentialRentRoll' => 'RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll',
    ];

    /**
     * @var array
     */
    protected $serializerGroups = ['MRI'];

    /**
     * @var array
     */
    protected $deserializerGroups = ['MRI-Response'];

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
     * @param HttpClient $httpClient
     * @param Logger $logger
     */
    public function __construct(
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        HttpClient $httpClient,
        Logger $logger
    ) {
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->httpClient = $httpClient
            ->setConfig(['timeout' => self::OPERATION_TIMEOUT])
            ->setNumberRetries(self::MAX_NUMBER_RETRIES);
        $this->logger = $logger;
    }

    /**
     * @TODO When will be solved problem with soap builder by besimple bundle it's must be removed
     */
    public function build()
    {
    }

    /**
     * @return bool
     */
    public function supportsBatches()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsProperties()
    {
        return true;
    }

    /**
     * @param $method
     * @param  array       $params
     * @return MRIResponse
     */
    public function sendRequest($method, array $params)
    {
        try {
            $baseParams = [
                '$api' => $method,
                '$format' => 'xml'
            ];

            $httpMethod = $this->getValueFromParameters($params, 'httpMethod', 'get');
            $body = $this->getValueFromParameters($params, 'body', null);

            /** @var MRISettings $mriSettings */
            $mriSettings = $this->getSettings();
            $authorization = $mriSettings->getParameters()['authorization'];
            $GETParameters = array_merge($baseParams, $params);
            $headers = [
                'Authorization' => sprintf('Basic %s', $authorization),
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
            ];
            $uri = sprintf(
                '%s?%s',
                $mriSettings->getUrl(),
                http_build_query($GETParameters)
            );

            return $this->manageResponse($this->httpClient->send($httpMethod, $uri, $headers, $body), $method);
        } catch (\Exception $e) {
            $this->logger->alert(
                sprintf(
                    '%sError message: %s In file: %s By line: %s',
                    static::LOG_PREFIX,
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
     * @param Response|null $response
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    protected function manageResponse($response, $method)
    {
        if (!$response instanceof Response) {
            throw new \Exception('Invalid response');
        }
        $httpCode = $response->getStatusCode();
        $body = $response->getBody();

        if ($httpCode !== 200) {
            throw new \Exception(sprintf('Bad http code(%s) from MRI', $httpCode));
        }

        $context = new DeserializationContext();
        $context->setGroups($this->deserializerGroups);

        $responseClass = $this->serializer->deserialize(
            $body->__toString(),
            $this->mappingSerialize[$method],
            'xml',
            $context
        );

        if (!($responseClass instanceof $this->mappingSerialize[$method]) || !is_object($responseClass)) {
            throw new \Exception(
                sprintf(
                    'Can\'t deserialize to class %s this body:%s',
                    $this->mappingSerialize[$method],
                    $body->__toString()
                )
            );

        }

        return $responseClass;
    }

    /**
     * @param mixed $mriResponse
     * @return MRIResponse
     */
    protected function checkResponseResidentTransactions($mriResponse)
    {
        if (!$mriResponse instanceof MRIResponse) {
            $mriResponse = new MRIResponse();
        }

        $this->logger->debug(
            sprintf(
                '%sMRI api call "getResidentTransactions" return %s number of Transactions',
                static::LOG_PREFIX,
                count($mriResponse->getValues())
            )
        );

        return $mriResponse;
    }

    /**
     * @param string $nextPageLink
     * @return MRIResponse
     */
    public function getResidentTransactionsByNextPageLink($nextPageLink)
    {
        $this->logger->debug(
            sprintf(
                '%sGo to the next page of MRI by link: %s',
                static::LOG_PREFIX,
                $nextPageLink
            )
        );
        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';
        $urlQuery = parse_url($nextPageLink, PHP_URL_QUERY);
        $urlQuery = str_replace(['&amp;'], ['&'], $urlQuery);
        parse_str($urlQuery, $nextPageParams);
        $mriResponse = $this->sendRequest($method, $nextPageParams);

        return $this->checkResponseResidentTransactions($mriResponse);
    }

    /**
     * @param string $externalPropertyId
     * @return MRIResponse
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $this->logger->debug(
            sprintf(
                '%sMRI api call getResidentTransactions for property ID: %s',
                static::LOG_PREFIX,
                $externalPropertyId
            )
        );
        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';
        $params = [
            'RMPROPID' => $externalPropertyId
        ];
        $mriResponse = $this->sendRequest($method, $params);

        return $this->checkResponseResidentTransactions($mriResponse);
    }

    /**
     * @param $externalPropertyId
     * @param string|null $buildingId
     * @param string|null $unitId
     * @return ResidentialRentRoll
     */
    public function getResidentialRentRoll($externalPropertyId, $buildingId = null, $unitId = null)
    {
        $this->logger->debug(
            sprintf(
                '%sMRI api call getResidentialRentRoll for property ID "%s"%s%s',
                static::LOG_PREFIX,
                $externalPropertyId,
                $buildingId ? ', building ID "' . $buildingId . '"' : '',
                $unitId ? ', unit ID "' . $unitId . '"' : ''
            )
        );
        $method = 'MRI_S-PMRM_ResidentialRentRoll';
        $params = [
            'PROPERTYID' => $externalPropertyId,
            'BUILDINGID' => $buildingId,
            'UNITID'     => $unitId
        ];

        return $this->sendRequest($method, $params);
    }

    /**
     * @param string $nextPageLink
     * @return ResidentialRentRoll
     */
    public function getResidentialRentRollByNextPageLink($nextPageLink)
    {
        $this->logger->debug(
            sprintf(
                '%sGo to the next page of MRI by link: %s',
                static::LOG_PREFIX,
                $nextPageLink
            )
        );
        $method = 'MRI_S-PMRM_ResidentialRentRoll';
        $urlQuery = parse_url($nextPageLink, PHP_URL_QUERY);
        $urlQuery = str_replace(['&amp;'], ['&'], $urlQuery);
        parse_str($urlQuery, $nextPageParams);

        return $this->sendRequest($method, $nextPageParams);
    }

    /**
     * @param  Order $order
     * @param $externalPropertyId
     * @return bool
     * @throws \Exception
     */
    public function postPayment(Order $order, $externalPropertyId)
    {
        $this->logger->debug(
            sprintf(
                '%sMRI api call postPayment for property ID "%s" and order #%d',
                static::LOG_PREFIX,
                $externalPropertyId,
                $order->getId()
            )
        );
        $payment = new Payment();
        $mriOrder = new MRIOrder($order);
        $payment->setEntryRequest($mriOrder);
        $paymentString = $this->paymentToStringFormat($payment, 'xml');

        $method = 'MRI_S-PMRM_PaymentDetailsByPropertyID';
        $params = [
            'RMPROPID' => $externalPropertyId,
            'body'  => $paymentString,
            'httpMethod' => 'post'
        ];

        /** @var Payment $payment */
        $payment = $this->sendRequest($method, $params);
        if ($payment instanceof Payment) {
            $error = $payment->getEntryResponse()->getError();

            if (!empty($error)) {
                $message = sprintf(
                    '%sMRI: Failed posting order(ID#%d). Error message: %s',
                    static::LOG_PREFIX,
                    $order->getId(),
                    $error->getMessage()
                );
                $this->logger->alert($message); // TODO: replace alert with exception. See RT-1449
                throw new \Exception($message);
            }

            return true;
        }

        return false;
    }

    /**
     * @param  Payment $payment
     * @param  string $format
     * @return string
     */
    protected function paymentToStringFormat(Payment $payment, $format)
    {
        $groups = $this->serializerGroups;
        if ($payment->getEntryRequest()->isSendDescription()) {
            $groups[] = 'MRI-with-description';
        }
        $context = SerializerXmlHelper::getSerializerContext($groups, true);

        $paymentXml = $this->serializer->serialize(
            $payment,
            $format,
            $context
        );

        $paymentXml = SerializerXmlHelper::removeStandartHeaderXml($paymentXml);

        return $paymentXml;
    }

    /**
     * @param $params
     * @param $key
     * @param $defaulValue
     * @return mixed
     */
    private function getValueFromParameters(&$params, $key, $defaulValue)
    {
        if (isset($params[$key])) {
            $val = $params[$key];
            unset($params[$key]);

            return $val;
        }

        return $defaulValue;
    }
}
