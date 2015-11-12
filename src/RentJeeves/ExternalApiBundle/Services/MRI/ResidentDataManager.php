<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ResidentDataManagerInterface;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * @Service("mri.resident_data")
 */
class ResidentDataManager implements ResidentDataManagerInterface
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
     * @return Value[]
     */
    public function getResidentsByNextPageLink($nextPageLink)
    {
        $this->logger->debug(sprintf('Get MRI Residents by page:%s', $nextPageLink));
        $mriResponse = $this->client->getResidentTransactionsByNextPageLink($nextPageLink);

        $this->assertSuccessfulResponse($mriResponse, 'getResidentTransactionsByNextPageLink');

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return Value[]
     */
    public function getResidentsFirstPage($externalPropertyId)
    {
        $this->logger->debug(sprintf('Get MRI Residents by external property ID:%s', $externalPropertyId));
        $mriResponse = $this->client->getResidentTransactions($externalPropertyId);

        $this->assertSuccessfulResponse($mriResponse, 'getResidentTransactions');

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return Value[]
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $residentTransactions = $this->getResidentsFirstPage($externalPropertyId);
        while ($nextPageLink = $this->getNextPageLink()) {
            $residentTransactionsByNextPageLink = $this->getResidentsByNextPageLink(
                $nextPageLink
            );
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        return $residentTransactions;
    }

    /**
     * @param string $nextPageLink
     * @return Value[]
     */
    public function getResidentsRentRollByNextPageLink($nextPageLink)
    {
        $this->logger->debug(sprintf('Get MRI Residents RentRoll by page:%s', $nextPageLink));
        $mriResponse = $this->client->getResidentialRentRollByNextPageLink($nextPageLink);

        $this->assertSuccessfulResponse($mriResponse, 'getResidentialRentRollByNextPageLink');

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return Value[]
     */
    public function getResidentsRentRollFirstPage($externalPropertyId)
    {
        $this->logger->debug(sprintf('Get MRI Residents RentRoll by external property ID:%s', $externalPropertyId));
        $mriResponse = $this->client->getResidentialRentRoll($externalPropertyId);

        $this->assertSuccessfulResponse($mriResponse, 'getResidentialRentRoll');

        $this->setNextPageLink($mriResponse->getNextPageLink());

        return $mriResponse->getValues();
    }

    /**
     * @param string $externalPropertyId
     * @return Value[]
     */
    public function getResidentsWithRecurringCharges($externalPropertyId)
    {
        $residentTransactions = $this->getResidentsRentRollFirstPage($externalPropertyId);
        while ($nextPageLink = $this->getNextPageLink()) {
            $this->logger->info(sprintf('MRIRentSync: get residents RentRoll by next page link %s', $nextPageLink));
            $residentTransactionsByNextPageLink = $this->getResidentsRentRollByNextPageLink(
                $nextPageLink
            );
            $residentTransactions = array_merge($residentTransactions, $residentTransactionsByNextPageLink);
        }

        return $residentTransactions;
    }

    /**
     *
     * Throw an exception if the MRI client returns a false (which indicates a failure)
     *
     * TODO: this really should be thrown by the client itself and caught at the controller/console-command level
     *
     * @param mixed $mriResponse
     * @param string $externalMethodName name of client method just called
     *
     * @throws \RuntimeException if $mriResponse is false which indicates a failure
     */
    protected function assertSuccessfulResponse($mriResponse, $externalMethodName)
    {
        if ($mriResponse === false) {
            throw new \RuntimeException(sprintf('MRI Client call failed to %s.', $externalMethodName));
        }
    }
}
