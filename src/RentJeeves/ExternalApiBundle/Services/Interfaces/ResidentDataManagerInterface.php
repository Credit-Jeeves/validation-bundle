<?php

namespace RentJeeves\ExternalApiBundle\Services\Interfaces;

interface ResidentDataManagerInterface
{
    /**
     * @param SettingsInterface $settings
     */
    public function setSettings(SettingsInterface $settings);

    /**
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidentTransactions($externalPropertyId);

    /**
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidentsWithRecurringCharges($externalPropertyId);
}
