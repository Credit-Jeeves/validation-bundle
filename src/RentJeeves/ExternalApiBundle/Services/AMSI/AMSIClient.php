<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\SoapDebuggableTrait as SoapDebug;
use RentJeeves\ExternalApiBundle\Traits\StandartDebuggableTrait as Debug;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait as Settings;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use Exception;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use RentJeeves\ExternalApiBundle\Traits\SoapExceptionLoggableTrait as SoapExceptionLog;
use Symfony\Bridge\Monolog\Logger;

/**
 * @Service("soap.client.amsi")
 */
class AMSIClient implements ClientInterface
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
     * @var SoapClientBuilder
     */
    protected $soapClientBuilder;

    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @InjectParams({
     *     "logger"            = @Inject("logger"),
     *     "wsdlRenderer"      = @Inject("soap.wsdl.twig.renderer"),
     *     "soapClientBuilder" = @Inject("besimple.soap.client.amsi"),
     *     "exceptionCatcher"  = @Inject("fp_badaboom.exception_catcher"),
     *     "serializer"        = @Inject("jms_serializer"),
     * })
     *
     * @param Logger $logger
     * @param SoapWsdlTwigRenderer $wsdlRenderer
     * @param SoapClientBuilder $soapClientBuilder
     * @param ExceptionCatcher $exceptionCatcher
     * @param Serializer $serializer
     */
    public function __construct(
        Logger $logger,
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer
    ) {
        $this->logger = $logger;
        $this->soapClientBuilder = $soapClientBuilder;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->exceptionCatcher = $exceptionCatcher;
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
            $this->debugMessage(
                sprintf(
                    "Call function(%s) with parameters: %s",
                    $function,
                    print_r($params, true)
                )
            );
            $response = $this->soapClient->__soapCall($function, $params);
            $this->debugMessage(
                sprintf(
                    "Response: %s",
                    print_r($response, true)
                )
            );
        } catch (Exception $e) {
            $this->exceptionLog($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canWorkWithBatches()
    {
        return true;
    }
}
