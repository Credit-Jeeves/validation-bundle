<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentTransactionsLoginResponse;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetUnitInformationResponse;
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
    public function shouldGetResidentLeaseCharges()
    {
        $client = $this->getClient();
        /** @var $response ResidentLeaseChargesLoginResponse */
        $response = $client->getResidentLeaseCharges('rnttrk01');
        $this->assertTrue($response instanceof ResidentLeaseChargesLoginResponse);
        $this->assertNotEmpty($property = $response->getProperty(), 'Property not found in response.');
        $this->assertNotEmpty($customers = $property->getCustomers(), 'Customers not found in property.');
        $customer = reset($customers);
        $this->assertNotEmpty(
            $serviceTransactions = $customer->getServiceTransactions(),
            'ServiceTransactions not found in customer'
        );
        $transactions = $serviceTransactions->getTransactions();

        $transaction = reset($transactions);
        $this->assertNotEmpty($charge = $transaction->getCharge(), 'Charge not found in transaction');
        $this->assertNotEmpty($detail = $charge->getDetail(), 'Detail not found in charge');
        $this->assertNotEmpty($detail->getAmount(), 'Amount is empty on detail');
        $this->assertNotEmpty($detail->getUnitID(), 'UnitId is empty on detail');
        $this->assertNotEmpty($detail->getChargeCode(), 'ChargeCode is empty on detail');
        $this->assertNotEmpty($detail->getCustomerID(), 'CustomerID is empty on detail');
    }

    /**
     * @test
     */
    public function getUnitInformation()
    {
        $client = $this->getClient();
        $response = $client->getUnitInformation('rnttrk01');

        $this->assertTrue(
            $response instanceof GetUnitInformationResponse,
            'Response should be mapped to GetUnitInformationResponse'
        );

        $this->assertNotEmpty($property = $response->getProperty(), 'Should be mapped Property');
        $this->assertNotEmpty($customers = $property->getCustomers(), 'Should be mapped Customers');
        $this->assertNotEmpty($customers[0]->getCustomerId(), 'Should be mapped Customer ID');
        $this->assertNotEmpty($fullUnitInfo = $customers[0]->getUnit(), 'Should be mapped Full Unit Info');
        $this->assertNotEmpty($fullUnitInfo->getUnitId(), 'Should be mapped Unit ID');
        $this->assertNotEmpty($fullUnitInfo->getUnitIdValue(), 'Should be mapped Unit ID Value');
        $this->assertNotEmpty($unit = $fullUnitInfo->getUnit(), 'Should be mapped Unit');
        $this->assertNotEmpty($unit->getMarketingName(), 'Should be mapped Marketing Name');
        $this->assertNotEmpty($unit->getPropertyPrimaryId(), 'Should be mapped Property Primary ID');
        $this->assertNotEmpty($information = $unit->getInformation(), 'Should be mapped Information');
        $this->assertNotEmpty($information->getUnitId(), 'Should be mapped Information Unit ID');
        $this->assertNotEmpty($address = $information->getAddress(), 'Should be mapped Information Address');
        $this->assertNotEmpty($address->getAddress1(), 'Should be mapped Information Address1');
    }
}
