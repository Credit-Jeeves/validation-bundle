<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\DeserializationContext;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse;
use RentJeeves\ExternalApiBundle\Model\MRI\Payment;
use RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll;
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
     * @param Serializer       $serializer
     * @param Logger           $logger
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
            $this->debugMessage(sprintf("Setup MRI headers %s", print_r($headers, true)));
            $uri = sprintf(
                '%s?%s',
                $mriSettings->getUrl(),
                http_build_query($GETParameters)
            );

            $this->debugMessage(sprintf("Request to MRI by uri %s", $uri));
            $request = $this->httpClient->$httpMethod($uri, $headers);

            if (!empty($body)) {
                $this->debugMessage(sprintf("Setup body to MRI: %s", $body));
                $request->setBody($body);
            }

            return $this->manageResponse($this->httpClient->send($request), $method);
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
     * @return mixed
     * @throws Exception
     */
    protected function manageResponse($response, $method)
    {
        $httpCode = $response->getStatusCode();
        $body = $response->getBody();
        $this->debugMessage(sprintf('Http code: %s', $httpCode));
        $this->debugMessage(sprintf('Body: %s', $body));

        if ($httpCode !== 200) {
            throw new Exception(sprintf("Bad http code(%s) from MRI", $httpCode));
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
            throw new Exception(
                sprintf(
                    "Can't deserialize to class %s this body:%s",
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
                'MRI api call "getResidentTransactions" return %s number of Transactions',
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
        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';
        $this->logger->debug(sprintf('Go to the next page of MRI by link: %s', $nextPageLink));
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
            sprintf('MRI api call getResidentTransactions for property ID: %s', $externalPropertyId)
        );
        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';
        $params = [
            'RMPROPID' => $externalPropertyId
        ];

        $this->debugMessage(sprintf('Call MRI method: %s', $method));

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
            sprintf('MRI api call getResidentialRentRoll for property ID: %s', $externalPropertyId)
        );
        $method = 'MRI_S-PMRM_ResidentialRentRoll';
        $params = [
            'PROPERTYID' => $externalPropertyId,
            'BUILDINGID' => $buildingId,
            'UNITID'     => $unitId
        ];

        $this->debugMessage(sprintf('Call MRI method: %s', $method));

        return $this->sendRequest($method, $params);
    }

    /**
     * @param string $nextPageLink
     * @return ResidentialRentRoll
     */
    public function getResidentialRentRollByNextPageLink($nextPageLink)
    {
        $method = 'MRI_S-PMRM_ResidentialRentRoll';
        $this->logger->debug(sprintf('Go to the next page of MRI by link: %s', $nextPageLink));
        $urlQuery = parse_url($nextPageLink, PHP_URL_QUERY);
        $urlQuery = str_replace(['&amp;'], ['&'], $urlQuery);
        parse_str($urlQuery, $nextPageParams);

        return $this->sendRequest($method, $nextPageParams);
    }

    /**
     * @param  Order $order
     * @param $externalPropertyId
     * @return bool
     */
    public function postPayment(Order $order, $externalPropertyId)
    {
        $payment = new Payment();
        $payment->setEntryRequest($order);
        $paymentString = $this->paymentToStringFormat($payment, 'xml');

        $method = 'MRI_S-PMRM_PaymentDetailsByPropertyID';

        $params = [
            'RMPROPID' => $externalPropertyId,
            'body'  => $paymentString,
            'httpMethod' => 'post'
        ];

        $this->debugMessage("Call MRI method: {$method}");
        /** @var Payment $payment */
        $payment = $this->sendRequest($method, $params);
        if ($payment instanceof Payment) {
            $error = $payment->getEntryResponse()->getError();

            if (!empty($error)) {
                $message = sprintf(
                    'MRI: Failed posting order(ID#%d). Error message: %s',
                    $order->getId(),
                    $error->getMessage()
                );
                $this->logger->alert($message); // TODO: replace alert with exception. See RT-1449
                throw new Exception($message);
            }

            return true;
        }

        return false;
    }

    /**
     * @param  Payment $payment
     * @return string
     */
    public function paymentToStringFormat(Payment $payment, $format)
    {
        $context = SerializerXmlHelper::getSerializerContext($this->serializerGroups, true);

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
