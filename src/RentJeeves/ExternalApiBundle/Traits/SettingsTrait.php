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
     * @return SettingsInterface
     */
    public function getSettings()
    {
        if (!$this->settings instanceof SettingsInterface) {
            throw new \LogicException('Should set settings at first');
        }

        return $this->settings;
    }
}
