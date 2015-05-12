<?php

namespace RentJeeves\ExternalApiBundle\Services\ResMan;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customers;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Traits\SettingsTrait;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * @Service("resman.resident_data")
 */
class ResidentDataManager
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
     * @InjectParams({
     *     "client" = @Inject("resman.client"),
     *     "logger" = @Inject("logger")
     * })
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
     * @return array
     */
    public function getResidents($externalPropertyId)
    {
        $residents = $this->client->getResidentTransactions($externalPropertyId);
        $customers = $residents->getProperty()->getRtCustomers();
        $self = $this;

        /** @var $customer RtCustomer  */

        return array_filter($customers, function (RtCustomer $customer) use ($self) {
            $customers = $customer->getCustomers();

            if (($customers instanceof Customers) === false) {
                $customer = print_r($customer, true);
                $this->logger->warning(sprintf("Skipping entry from resman with no tenants: %s", $customer));

                return false;
            }

            return true;
        });
    }
}
