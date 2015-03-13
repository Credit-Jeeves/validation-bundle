<?php

namespace RentJeeves\ExternalApiBundle\Services\Interfaces;

interface ClientInterface
{
    public function build();

    public function setSettings(SettingsInterface $settings);

    public function getSettings();

    public function sendRequest($function, array $params);

    public function isWorksWithBatchs();
}
