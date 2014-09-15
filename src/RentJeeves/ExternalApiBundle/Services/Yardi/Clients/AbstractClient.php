<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Services\SoapClientInterface;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapSettingsInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use Exception;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\Serializer\Serializer;
use \AppRjKernel as Kernel;

abstract class AbstractClient implements SoapClientInterface
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @var SoapClientBuilder
     */
    protected $soapClientBuilder;

    /**
     * @var YardiSettings
     */
    protected $settings;

    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var string
     */
    protected $license;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Messages
     */
    protected $messages;

    protected $kernel;

    /**
     * @param SoapClient $soapClient
     */
    public function __construct(
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        ExceptionCatcher $exceptionCatcher,
        Serializer $serializer,
        Kernel $kernel,
        $entity,
        $license
    ) {
        $this->soapClientBuilder = $soapClientBuilder;
        $this->entity = $entity;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->license = $license;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->serializer = $serializer;
        $this->kernel = $kernel;
    }

    public function build()
    {
        $this->soapClientBuilder->setSettings($this->getSettings());
        $this->soapClientBuilder->setWsdlRenderer($this->wsdlRenderer);
        $this->soapClient = $this->soapClientBuilder->build();

        return $this;
    }

    /**
     * @param SoapSettingsInterface $settings
     */
    public function setSettings(SoapSettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return YardiSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return array(
            "UserName"          => $this->settings->getUsername(),
            "Password"          => $this->settings->getPassword(),
            "ServerName"        => $this->settings->getDatabaseServer(),
            "Database"          => $this->settings->getDatabaseName(),
            "Platform"          => $this->settings->getPlatform(),
            "InterfaceEntity"   => $this->entity,
            "InterfaceLicense"  => $this->getLicense(),
        );
    }

    /**
     * @return string
     */
    protected function getLicense()
    {
        $path = $this->kernel->locateResource(
            '@ExternalApiBundle/Resources/files/'.$this->license
        );
        return file_get_contents($path);
    }

    /**
     * @param null $xml
     * @return bool
     */
    public function isError($xml = null)
    {
        if (is_null($xml)) {
            if ($this->messages) {
                return true;
            }

            return false;
        }

        $this->messages = $this->serializer->deserialize(
            $xml,
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages',
            'xml'
        );

        if (!is_null($this->messages->getMessage())) {
            return true;
        }

        $this->messages = null;

        return false;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        if ($this->messages) {
            return $this->messages->getMessage();
        }

        return null;
    }


    /**
     * @param string $function
     * @param array $params
     * @param string $deserializeClass
     *
     * @return mixed
     */
    protected function processRequest(
        $function,
        array $params,
        $responseField = null,
        $deserializeClass = null
    ) {
        try {
            $responce = $this->soapClient->__soapCall($function, $params);

            //When response is xml
            if (isset($responce->$responseField->any)) {
                $xml = $responce->$responseField->any;
                $xml = '<?xml version="1.0" encoding="UTF-8"?>'.$xml;
            //When response just string or integer
            } elseif (isset($responce->$responseField)) {
                return $responce->$responseField;
            } else {
                throw new Exception("Bad Response: ".$this->soapClient->__getLastResponse());
            }

            if (!isset($xml)) {
                return $responce;
            }

            if ($this->isError($xml)) {
                return null;
            }

            if (empty($deserializeClass)) {
                return $xml;
            }
            $result = $this->serializer->deserialize(
                $xml,
                $deserializeClass,
                'xml'
            );
            return $result;
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            $this->messages = new Messages();
            $this->messages->setMessage($e->getMessage());
        }

        return null;
    }
}
