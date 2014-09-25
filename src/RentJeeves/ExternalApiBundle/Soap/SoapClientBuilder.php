<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use BeSimple\SoapBundle\Soap\SoapClientBuilder as Base;
use RentJeeves\ExternalApiBundle\Soap\SoapClient as Client;

class SoapClientBuilder extends Base
{
    protected $isBuild = false;

    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var SoapSettingsInterface
     */
    protected $settings;

    /**
     * @param SoapWsdlTwigRenderer $wsdlRenderer
     * @return $this
     */
    public function setWsdlRenderer(SoapWsdlTwigRenderer $wsdlRenderer)
    {
        $this->wsdlRenderer = $wsdlRenderer;
        return $this;
    }

    /**
     * @param SoapSettingsInterface $settings
     * @return $this
     */
    public function setSettings(SoapSettingsInterface $settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Finally returns a SoapClient instance.
     *
     * @return Client
     */
    public function build()
    {
        if ($this->isBuild === false) {
            $this->isBuild = true;
            return $this;
        }
        $this->validateOptions();
        return new Client(
            $this->wsdl,
            $this->getSoapOptions(),
            $this->wsdlRenderer,
            $this->settings
        );
    }
}
