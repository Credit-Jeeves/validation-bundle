<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use Doctrine\Common\Collections\ArrayCollection;
use Fp\BadaBoomBundle\ExceptionCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Unit;
use RentJeeves\ExternalApiBundle\Services\AMSI\AMSIClient;
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
     * @param $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->debug(sprintf("Get AMSI Residents by external property ID:%s", $externalPropertyId));
        $client = $this->getApiClient();
        $propertyResidentsC = $client->getPropertyResidents($externalPropertyId, $leaseStatus = 'C'); // (C)urrent
        $propertyResidentsN = $client->getPropertyResidents($externalPropertyId, $leaseStatus = 'N'); //(N)otice

        $leases = array_merge($propertyResidentsC->getLease(), $propertyResidentsN->getLease());

        $units = $client->getPropertyUnits($externalPropertyId);
        /** @var Unit $unit */
        foreach ($units as $key => $unit) {
            unset($units[$key]);
            $units[$unit->getUnitId()] = $unit;
        }
        /** @var Lease $lease */
        foreach ($leases as $lease)
        {
            $lease->setUnit($units[$lease->getUnitId()]);
        }

        return $leases;
    }

    /**
     * @return AMSIClient
     */
    protected function getApiClient()
    {
        return $this->clientFactory->getClient(
            $this->settings,
            SoapClientEnum::AMSI_CLIENT
        );
    }
}
