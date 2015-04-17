<?php

namespace RentJeeves\ExternalApiBundle\Services\Interfaces;

interface ClientInterface
{
    /**
     * This function must setup settings to client
     *
     * @return this
     */
    public function build();

    /**
     * @param SettingsInterface $settings
     * @return this
     */
    public function setSettings(SettingsInterface $settings);

    /**
     * @return SettingsInterface
     */
    public function getSettings();

    /**
     * @param $function
     * @param array $params
     * @return mixed
     */
    public function sendRequest($function, array $params);

    /**
     * @return boolean
     */
    public function supportsBatches();

    /**
     * @return boolean
     */
    public function supportsProperties();
}
