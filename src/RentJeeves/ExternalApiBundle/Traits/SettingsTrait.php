<?php

namespace RentJeeves\ExternalApiBundle\Traits;

use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

trait SettingsTrait
{
    /**
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * @param SettingsInterface $settings
     */
    public function setSettings(SettingsInterface $settings)
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
}
