<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Unit;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILeasingClient;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * @DI\Service("amsi.resident_data")
 */
class ResidentDataManager
{
    use SettingsTrait;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "clientFactory" = @DI\Inject("soap.client.factory"),
     *     "logger" = @DI\Inject("logger")
     * })
     */
    public function __construct(
        SoapClientFactory $clientFactory,
        Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * @param SettingsInterface $settings
     */
    public function setSettings(SettingsInterface $settings)
    {
        $this->logger->debug(sprintf("Setup AMSI settings ID:%s", $settings->getId()));
        $this->settings = $settings;
    }

    /**
     * @param string $externalPropertyId
     * @return array
     * @throws \LogicException
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->debug(sprintf('Get AMSI Residents by external property ID:%s', $externalPropertyId));
        $client = $this->getApiClient(SoapClientEnum::AMSI_LEASING);
        $currentResidents = $client->getPropertyResidents($externalPropertyId, Lease::STATUS_CURRENT);
        $residentsOnNotice = $client->getPropertyResidents($externalPropertyId, Lease::STATUS_NOTICE);

        $leases = array_merge($currentResidents->getLease(), $residentsOnNotice->getLease());

        if (empty($leases)) {
            throw new \LogicException('AMSI client return empty resident\'s list. Can\'t process anymore.');
        }

        $units = $client->getPropertyUnits($externalPropertyId);
        $unitsLookup = [];
        /** @var Unit $unit */
        foreach ($units as $key => $unit) {
            $this->logger->debug(sprintf('Unit File ID: %s', $unit->getUnitId()));
            $unitsLookup[$unit->getUnitId()] = $unit;
        }

        /** @var Lease $lease */
        foreach ($leases as $lease) {
            $this->logger->debug(sprintf('Lease UnitId: %s', $lease->getUnitId()));
            $lease->setUnit($unitsLookup[$lease->getUnitId()]);
        }
        $this->logger->debug('Unit mapping complete.');

        return $leases;
    }

    /**
     * @return AMSILeasingClient|AMSILedgerClient
     */
    protected function getApiClient($apiType)
    {
        return $this->clientFactory->getClient($this->settings, $apiType);
    }
}
