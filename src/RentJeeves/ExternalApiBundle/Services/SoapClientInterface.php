<?php

namespace RentJeeves\ExternalApiBundle\Services;

use RentJeeves\ExternalApiBundle\Soap\SoapSettingsInterface;

interface SoapClientInterface
{
    public function build();

    public function setSettings(SoapSettingsInterface $settings);

    public function getSettings();
}
