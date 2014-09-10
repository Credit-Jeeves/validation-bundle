<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Clients;

use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\ExternalApiBundle\Services\SoapClientInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder;
use RentJeeves\ExternalApiBundle\Soap\SoapSettingsInterface;
use RentJeeves\ExternalApiBundle\Soap\SoapClient;
use Exception;
use RentJeeves\ExternalApiBundle\Soap\SoapWsdlTwigRenderer;

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

    protected $exceptionCatcher;

    /**
     * @param SoapClient $soapClient
     */
    public function __construct(
        SoapWsdlTwigRenderer $wsdlRenderer,
        SoapClientBuilder $soapClientBuilder,
        $exceptionCatcher,
        $entity,
        $license
    ) {
        $this->soapClientBuilder = $soapClientBuilder;
        $this->entity = $entity;
        $this->wsdlRenderer = $wsdlRenderer;
        $this->license = $license;
        $this->exceptionCatcher = $exceptionCatcher;
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
        return file_get_contents($this->license);
    }

    protected function processRequest($function, $params)
    {
        try {
            return  $this->soapClient->__soapCall($function, $params);
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
        }
    }
}
