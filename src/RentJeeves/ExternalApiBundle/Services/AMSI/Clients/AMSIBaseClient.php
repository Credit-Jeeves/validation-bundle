<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;
use RentJeeves\ExternalApiBundle\Traits\SoapDebuggableTrait as SoapDebug;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use RentJeeves\ExternalApiBundle\Traits\SoapExceptionLoggableTrait as SoapExceptionLog;


class AMSIBaseClient implements ClientInterface
{
    use Debug;
    use Settings;
    use SoapDebug;
    use SoapExceptionLog;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var SoapClientBuilder
     */
    protected $soapClientBuilder;

    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @param Logger $logger
     * @param SoapWsdlTwigRenderer $wsdlRenderer
     * @param SoapClientBuilder $soapClientBuilder
     * @param Serializer $serializer
     */
    public function __construct(
        Logger $logger,
        ExceptionCatcher $exceptionCatcher,
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        Serializer $serializer
    ) {
        $this->logger = $logger;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->soapClientBuilder = $soapClientBuilder;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $this->soapClientBuilder->setSettings($this->getSettings());
        $this->soapClientBuilder->setWsdlRenderer($this->wsdlRenderer);
        $this->soapClient = $this->soapClientBuilder->build();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest($function, array $params)
    {
        try {
            $this->debugMessage(sprintf('AMSI: call function %s with params: %s', $function, print_r($params, true)));

            $response = $this->soapClient->__soapCall($function, $params);

            $this->debugMessage(sprintf('AMSI: Response: %s', print_r($response, true)));

            $methodResult = sprintf('%sResult', $function);

            if (isset($response->$methodResult)) {
                return $response->$methodResult;
            }

            throw new \Exception(sprintf("AMSI: Response is wrong (%s)", print_r($response, true)));
        } catch (\Exception $e) {
            $this->exceptionLog($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canWorkWithBatches()
    {
        return false;
    }
}
