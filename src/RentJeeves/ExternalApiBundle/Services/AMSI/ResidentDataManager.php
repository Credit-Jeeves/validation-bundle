<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ExternalApiBundle\Model\AMSI\Lease;
use RentJeeves\ExternalApiBundle\Model\AMSI\Unit;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payment;
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

    const SUCCESSFUL_RESPONSE_CODE = 0;

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
     * @param  string $externalPropertyId
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->debug(sprintf("Get AMSI Residents by external property ID:%s", $externalPropertyId));
        $client = $this->getApiClient(SoapClientEnum::AMSI_LEASING);
        $currentResidents = $client->getPropertyResidents($externalPropertyId, $leaseStatus = 'C'); // (C)urrent
        $residentsOnNotice = $client->getPropertyResidents($externalPropertyId, $leaseStatus = 'N'); //(N)otice

        $leases = array_merge($currentResidents->getLease(), $residentsOnNotice->getLease());

        $units = $client->getPropertyUnits($externalPropertyId);
        /** @var Unit $unit */
        foreach ($units as $key => $unit) {
            unset($units[$key]);
            $units[$unit->getUnitId()] = $unit;
        }
        /** @var Lease $lease */
        foreach ($leases as $lease) {
            $lease->setUnit($units[$lease->getUnitId()]);
        }

        return $leases;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function postPayment(Order $order)
    {
        try {
            $client = $this->getApiClient(SoapClientEnum::AMSI_LEDGER);
            /** @var Payment $payment */
            $payment = $client->addPayment($order);
            if (self::SUCCESSFUL_RESPONSE_CODE == $payment->getErrorCode()) {
                return true;
            }
            $this->logger->critical(sprintf(
                'AMSI: Failed posting order(ID#%s). Got error code %d, error description %s',
                $order->getId(),
                $payment->getErrorCode(),
                $payment->getErrorDescription()
            ));

            return false;
        } catch (\Exception $e) {
            $this->logger->critical(sprintf(
                'AMSI: Failed posting order(ID#%s). Got exception %s',
                $order->getId(),
                $e->getMessage()
            ));
        }

        return false;
    }

    /**
     * @return AMSILeasingClient|AMSILedgerClient
     */
    protected function getApiClient($apiType)
    {
        return $this->clientFactory->getClient($this->settings, $apiType);
    }
}
