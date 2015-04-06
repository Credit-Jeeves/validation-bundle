<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\EDEX;
use RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ClientInterface;
use RentJeeves\ExternalApiBundle\Traits\SoapDebuggableTrait as SoapDebug;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait as Debug;
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
                    "Call AMSI function(%s) with parameters: %s",
                    $function,
                    print_r($params, true)
                )
            );
            $response = $this->soapClient->__soapCall($function, $params);
            $this->debugMessage(
                sprintf(
                    "Response AMSI: %s",
                    print_r($response, true)
                )
            );

            $methodResult = sprintf('%sResult', $function);

            if (isset($response->$methodResult)) {
                return $response->$methodResult;
            }

            throw new Exception(sprintf("Response AMSI is wrong (%s)", print_r($response, true)));
        } catch (Exception $e) {
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

    /**
     * @param string $propertyId
     * @return PropertyResidents
     * @throws Exception
     */
    public function getPropertyResidents($propertyId)
    {
        $result = $this->sendRequest(
            'GetPropertyResidents',
            $this->getParametersForPropertyResidents($propertyId)
        );

        $result = SerializerXmlHelper::replaceEscapeToCorrectSymbol($result);
        /** @var PropertyResidents $propertyResidents */
        $propertyResidents = $this->serializer->deserialize(
            $result,
            'RentJeeves\ExternalApiBundle\Model\AMSI\PropertyResidents',
            'xml',
            $this->getDeserializationContext()
        );

        if ($propertyResidents instanceof PropertyResidents && count($propertyResidents->getLease()) > 0) {
            return $propertyResidents;
        }

        throw new Exception(sprintf("Don't have data, when deserialize AMSI response (%s)", $result));
    }

    /**
     * @param string $propertyId
     * @return array
     */
    protected function getParametersForPropertyResidents($propertyId)
    {
        $edex = new EDEX();
        $edex->setPropertyId($propertyId);

        $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
            $this->serializer->serialize(
                $edex,
                'xml',
                $this->getSerializationContext()
            )
        );
        $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
        $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

        $parameters = [
            'GetPropertyResidents' => array_merge(
                $this->getLoginCredentials(),
                ['XMLData'=> new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return [
            'UserID'  => $this->settings->getUser(),
            'Password'=> $this->settings->getPassword(),
            'PortfolioName' => $this->settings->getPortfolioName(),
        ];
    }

    /**
     * @return SerializationContext
     */
    protected function getSerializationContext()
    {
        $serializerContext = new SerializationContext();
        $serializerContext->setGroups(['AMSI']);

        return $serializerContext;
    }

    /**
     * @return DeserializationContext
     */
    protected function getDeserializationContext()
    {
        $deserializerContext = new DeserializationContext();
        $deserializerContext->setGroups(['AMSI']);

        return $deserializerContext;
    }
}
