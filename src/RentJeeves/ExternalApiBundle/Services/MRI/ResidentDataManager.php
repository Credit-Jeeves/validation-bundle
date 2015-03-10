<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * @Service("mri.resident_data")
 */
class ResidentDataManager
{
    use SettingsTrait;

    /**
     * @var MRIClient
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @InjectParams({
     *     "client" = @Inject("mri.client"),
     *     "logger" = @Inject("logger")
     * })
     */
    public function __construct(MRIClient $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param SettingsInterface $settings
     */
    public function setSettings(SettingsInterface $settings)
    {
        $this->logger->addInfo(sprintf("Setup settings ID:%s", $settings->getId()));
        $this->settings = $settings;
        $this->client->setSettings($settings);
    }

    /**
     * @param $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->addInfo(sprintf("Get Residents by external property ID:%s", $externalPropertyId));
        $mriResponse = $this->client->getResidentTransactions($externalPropertyId);

        return $mriResponse->getValues();
    }
}
