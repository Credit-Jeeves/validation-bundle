<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use BeSimple\SoapClient\SoapClient as Base;

class SoapClient extends Base
{
    /**
     * @var SoapWsdlTwigRenderer
     */
    protected $wsdlRenderer;

    /**
     * @var SoapSettingsInterface
     */
    protected $settings;

    /**
     * @param string $wsdl
     * @param array $options
     */
    public function __construct(
        $wsdl,
        array $options,
        SoapWsdlTwigRenderer $wsdlRenderer = null,
        SoapSettingsInterface $settings = null
    ) {
        $this->settings = $settings;
        $this->wsdlRenderer = $wsdlRenderer;
        return parent::__construct($wsdl, $options);
    }



    protected function loadWsdl($wsdl, array $options)
    {
        if ($this->wsdlRenderer->isTwigTemplate($wsdl)) {
            return $this->wsdlRenderer->render(
                $this->settings,
                $wsdl
            );
        }

        return parent::loadWsdl($wsdl, $options);
    }
}
