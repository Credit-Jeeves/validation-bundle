<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseChargesLoginResponse;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\ClientCaseBase as Base;

class ResidentTransactionsClientCase extends Base
{
    /**
     * @return ResidentTransactionsClient
     */
    protected function getClient()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        return $clientFactory->getClient(
            $this->getYardiSettings(),
            SoapClientEnum::YARDI_RESIDENT_TRANSACTIONS
        );
    }

    /**
     * @test
     */
    public function getPropertyConfigurations()
    {
        $client = $this->getClient();
        $result = $client->getPropertyConfigurations();

        $this->assertTrue($result instanceof GetPropertyConfigurationsResponse);
    }

    /**
     * @test
     */
    public function getResidentTransactions()
    {
        $client = $this->getClient();
        /** @var $response GetResidentTransactionsLoginResponse */
        $response = $client->getResidentTransactions('rnttrk01');
        $this->assertTrue($response instanceof GetResidentTransactionsLoginResponse);
    }

    /**
     * @test
     */
    public function getResidentLeaseCharges()
    {
        $client = $this->getClient();
        /** @var $response ResidentLeaseChargesLoginResponse */
        $response = $client->getResidentLeaseCharges('rnttrk01');
        $this->assertTrue($response instanceof ResidentLeaseChargesLoginResponse);
        $this->assertNotEmpty($property = $response->getProperty());
        $this->assertNotEmpty($customers = $property->getCustomers());
        $customer = reset($customers);
        $this->assertNotEmpty($serviceTransactions = $customer->getServiceTransactions());
        $transactions = $serviceTransactions->getTransactions();

        $transaction = reset($transactions);
        var_dump($transaction);exit;
        $this->assertNotEmpty($charge = $transaction->getCharge());
        $this->assertNotEmpty($detail = $charge->getDetail());
        $this->assertNotEmpty($detail->getAmount());
        $this->assertNotEmpty($detail->getUnitID());
        $this->assertNotEmpty($detail->getChargeCode());
        $this->assertNotEmpty($detail->getCustomerID());
    }
}
