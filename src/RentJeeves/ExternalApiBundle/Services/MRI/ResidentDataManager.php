<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
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
     * @var string
     */
    protected $nextPageLink;

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
        $this->logger->debug(sprintf('Setup MRI settings ID:%s', $settings->getId()));
        $this->settings = $settings;
        $this->client->setSettings($settings);
    }

    /**
     * @return string
     */
    public function getNextPageLink()
    {
        return $this->nextPageLink;
    }

    /**
     * @param string $nextPageLink
     */
    protected function setNextPageLink($nextPageLink)
    {
        $this->nextPageLink = $nextPageLink;
    }

    /**
     * @param string $nextPageLink
     * @return array
     */
    public function getResidentsByNextPageLink($nextPageLink)
    {
        $this->logger->debug(sprintf('Get MRI Residents by page:%s', $nextPageLink));
        $mriResponse = $this->client->getResidentTransactionsByNextPageLink($nextPageLink);

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->debug(sprintf('Get MRI Residents by external property ID:%s', $externalPropertyId));
        $mriResponse = $this->client->getResidentTransactions($externalPropertyId);

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $nextPageLink
     * @return array
     */
    public function getResidentsRentRollByNextPageLink($nextPageLink)
    {
        $this->logger->debug(sprintf('Get MRI Residents RentRoll by page:%s', $nextPageLink));
        $mriResponse = $this->client->getResidentialRentRollByNextPageLink($nextPageLink);

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return array
     */
    public function getResidentsRentRoll($externalPropertyId)
    {
        $this->logger->debug(sprintf('Get MRI Residents RentRoll by external property ID:%s', $externalPropertyId));
        $mriResponse = $this->client->getResidentialRentRoll($externalPropertyId);

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }
}
