<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use RentJeeves\ExternalApiBundle\Model\ResMan\Customers;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transactions;
use RentJeeves\ExternalApiBundle\Services\Interfaces\ResidentDataManagerInterface;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * DI\Service("resman.resident_data")
 */
class ResidentDataManager implements ResidentDataManagerInterface
{
    use SettingsTrait;

    /**
     * @var ResManClient
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ResManClient $client
     * @param Logger $logger
     */
    public function __construct(ResManClient $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param SettingsInterface $settings
     */
    public function setSettings(SettingsInterface $settings)
    {
        $this->settings = $settings;
        $this->client->setSettings($settings);
    }

    /**
     * @param $externalPropertyId
     * @return RtCustomer[]a
     */
    public function getResidentTransactions($externalPropertyId)
    {
        $this->logger->debug(
            '[ResMan Resident Manager]Try to get transaction residents for external property id #' . $externalPropertyId
        );
        try {
            $residents = $this->client->getResidentTransactions($externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->alert(
                '[ResMan Resident Manager]ERROR:' . $e->getMessage()
            );

            return [];
        }

        /** @var $customers RtCustomer[]  */
        $customers = $residents->getProperty() ? $residents->getProperty()->getRtCustomers() : [];

        return array_filter($customers, function (RtCustomer $customer) {
            $customers = $customer->getCustomers();
            if (($customers instanceof Customers) === false) {
                $customer = print_r($customer, true);
                $this->logger->warning(
                    sprintf("[ResMan Resident Manager]Skipping entry from resman with no tenants:\n%s", $customer)
                );

                return false;
            }

            return true;
        });
    }

    /**
     * @param $externalPropertyId
     *
     * @return Transactions[]
     */
    public function getResidentsWithRecurringCharges($externalPropertyId)
    {
        $this->logger->debug(
            '[ResMan Resident Manager]Try to get transaction residents with recurring charges' .
            ' for external property id #' . $externalPropertyId
        );
        try {
            $residents = $this->client->getTransactionsForRecurringCharges($externalPropertyId, new \DateTime());
        } catch (\Exception $e) {
            $this->logger->alert(
                '[ResMan Resident Manager]ERROR:' . $e->getMessage()
            );

            return [];
        }
        /** @var RtCustomer[] $customers */
        $customers = $residents->getProperty() ? $residents->getProperty()->getRtCustomers() : [];

        $result = [];
        foreach ($customers as $customer) {
            if (null === $rtTransactions = $customer->getRtServiceTransactions()) {
                continue;
            }

            foreach ($rtTransactions->getTransactions() as $transaction) {
                if (true === $this->hasRecurringCharges($transaction)) {
                    $result[] = $rtTransactions;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param Transactions $transactions
     *
     * @return bool
     */
    protected function hasRecurringCharges(Transactions $transactions)
    {
        if ($transactions->getCharge() !== null || $transactions->getConcession() !== null) {
            return true;
        }

        return false;
    }
}
