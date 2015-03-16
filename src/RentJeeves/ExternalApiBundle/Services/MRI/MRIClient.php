<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use RentJeeves\ComponentBundle\Helper\SerializerHelper;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse;
use RentJeeves\ExternalApiBundle\Model\MRI\Payment;
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

    const FORMAT_JSON = 'json';

    const FORMAT_XML = 'xml';

    protected $mappingSerialize = [
        'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID' => 'RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse',
        'MRI_S-PMRM_PaymentDetailsByPropertyID' => 'RentJeeves\ExternalApiBundle\Model\MRI\Payment'
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
     * @var string
     */
    protected $format = self::FORMAT_JSON;

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
     * @return bool
     */
    public function isWorksWithBatchs()
    {
        return false;
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
                '$api' => $method,
                '$format' => $this->format
            ];

            $httpMethod = $this->getValueFromParameters($params, 'httpMethod', 'get');
            $body = $this->getValueFromParameters($params, 'body', null);

            /** @var MRISettings $mriSettings */
            $mriSettings = $this->getSettings();
            $authorization = $mriSettings->getParameters()['authorization'];
            $GETParameters = array_merge($baseParams, $params);
            $headers = [
                'Authorization' => sprintf('Basic %s', $authorization),
                'Accept' => 'application/'.$this->format,
                'Content-Type' => 'application/'.$this->format,
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
            $this->format,
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
     * @param $externalPropertyId
     * @return MRIResponse
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $this->format = self::FORMAT_JSON;

        $method = 'MRI_S-PMRM_ResidentLeaseDetailsByPropertyID';
        $params = [
            'RMPROPID' => $externalPropertyId
        ];

        $this->debugMessage("Call MRI method: {$method}");
        $response = $this->sendRequest($method, $params);

        return $response;
    }

    /**
     * @param Order $order
     * @param $externalPropertyId
     * @return bool
     */
    public function postPayment(Order $order, $externalPropertyId)
    {
        $this->format = self::FORMAT_XML;

        $payment = new Payment();
        $payment->setEntryRequest($order);
        $paymentString = $this->paymentToStringFormat($payment);

        $method = 'MRI_S-PMRM_PaymentDetailsByPropertyID';

        $params = [
            'RMPROPID' => $externalPropertyId,
            'body'  => $paymentString,
            'httpMethod' => 'post'
        ];

        $this->debugMessage("Call MRI method: {$method}");
        /** @var Payment $payment */
        $payment = $this->sendRequest($method, $params);
        $error = $payment->getEntryResponse()->getError();

        if (!empty($error)) {
            throw new Exception(sprintf("Api return error %s", $error->getMessage()));
        }

        return true;
    }

    /**
     * @param Payment $payment
     * @return string
     */
    public function paymentToStringFormat(Payment $payment)
    {
        $context = SerializerHelper::getSerializerContext($this->serializerGroups, true);

        $paymentXml = $this->serializer->serialize(
            $payment,
            $this->format,
            $context
        );

        $paymentXml = SerializerHelper::removeStandartHeaderXml($paymentXml);

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
