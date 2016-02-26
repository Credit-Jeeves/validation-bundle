<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LoggerInterface as Logger;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ResidentDataManagerInterface;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentDataClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;

/**
 * DI\Service("yardi.resident_data")
 */
class ResidentDataManager implements ResidentDataManagerInterface
{
    const CURRENT_RESIDENT = 'Current';

    const NOTICE_RESIDENT = 'Notice';

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
     * @param SoapClientFactory $clientFactory
     * @param Logger $logger
     */
    public function __construct(SoapClientFactory $clientFactory, Logger $logger)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * @param string $externalPropertyId
     * @return ResidentsResident[]
     * @throws \Exception
     */
    public function getResidents($externalPropertyId)
    {
        $this->logger->debug(
            '[Yardi Resident Manager]Try to get resident for external property id - ' . $externalPropertyId
        );
        $residentClient = $this->getApiClient();
        $residents = $residentClient->getResidents($externalPropertyId);

        if ($residentClient->isError()) {
            $this->logger->alert('[Yardi Resident Manager]Get error from yardi: ' . $residentClient->getErrorMessage());

            return [];
        }

        if (empty($residents) ||
            !$residents->getPropertyResidents() ||
            !$residents->getPropertyResidents()->getResidents()
        ) {
            $this->logger->alert('[Yardi Resident Manager]Can\'t get residents from yardi.');

            return [];
        }

        return $residents->getPropertyResidents()->getResidents()->getResidents();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @return ResidentsResident[]
     * @throws \Exception
     */
    public function getCurrentAndNoticesResidents($externalPropertyId)
    {
        $residents = $this->getResidents($externalPropertyId);

        $currentResidents = array_filter(
            $residents,
            function (ResidentsResident $resident) {
                return $resident->getStatus() == self::CURRENT_RESIDENT ||
                $resident->getStatus() == self::NOTICE_RESIDENT;
            }
        );

        return $currentResidents;
    }

    /**
     * @param Holding $holding
     * @param string $residentId
     * @param string $externalPropertyId
     * @return ResidentLeaseFile
     * @throws \Exception
     */
    public function getResidentData($residentId, $externalPropertyId)
    {
        $residentClient = $this->getApiClient();
        $resident = $residentClient->getResidentData($externalPropertyId, $residentId);

        if (empty($resident) || !$resident->getLeaseFiles()) {
            throw new \Exception(
                sprintf(
                    "Can't get resident data by resident ID '%s' and external property ID '%s'",
                    $residentId,
                    $externalPropertyId
                )
            );
        }

        return $resident->getLeaseFiles()->getLeaseFile();
    }

    /**
     * @param $externalPropertyId
     *
     * @return ResidentTransactionPropertyCustomer[]
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $this->logger->debug(
            '[Yardi Resident Manager]Try to get transaction residents for external property id #' . $externalPropertyId
        );
        $client = $this->getApiClient(SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS);

        $transactionData = $client->getResidentTransactions($externalPropertyId);

        if ($client->isError()) {
            $this->logger->alert('[Yardi Resident Manager]ERROR:' . $client->getErrorMessage());

            return [];
        }

        if (empty($transactionData) || !$transactionData->getProperty()) {
            $this->logger->alert('[Yardi Resident Manager]Can\'t get transaction residents from yardi.');

            return [];
        }

        return $transactionData->getProperty()->getCustomers();
    }

    /**
     * @param $externalPropertyId
     *
     * @return ResidentTransactionPropertyCustomer[]
     */
    public function getResidentsWithRecurringCharges($externalPropertyId)
    {
        $this->logger->debug(
            '[Yardi Resident Manager]Try to get transaction residents with recurring charges' .
            ' for external property id #' . $externalPropertyId
        );
        $client = $this->getApiClient(SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS);

        $transactionData = $client->getResidentLeaseCharges($externalPropertyId);

        if ($client->isError()) {
            $this->logger->alert('[Yardi Resident Manager]ERROR:' . $client->getErrorMessage());

            return [];
        }

        if (empty($transactionData) || !$transactionData->getProperty()) {
            $this->logger->alert('[Yardi Resident Manager]Can\'t get transaction residents from yardi.');

            return [];
        }

        return $transactionData->getProperty()->getCustomers();
    }

    /**
     * @return array|Soap\Property[]
     */
    public function getProperties()
    {
        $response = $this->getApiClient(SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS)->getPropertyConfigurations();
        if ($response) {
            return $response->getProperty();
        }

        return [];
    }

    /**
     * @param string $clientType
     * @return ResidentDataClient|ResidentTransactionsClient
     * @throws \Exception
     */
    protected function getApiClient($clientType = SoapClientEnum::YARDI_RESIDENT_DATA)
    {
        return $this->clientFactory->getClient(
            $this->getSettings(),
            $clientType
        );
    }
}
