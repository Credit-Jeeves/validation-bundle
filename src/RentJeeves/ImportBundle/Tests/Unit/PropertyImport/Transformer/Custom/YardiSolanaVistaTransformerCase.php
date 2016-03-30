<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Transformer\Custom;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\Yardi\FullResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\CustomerAddress;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customers;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionUnit;
use RentJeeves\ImportBundle\PropertyImport\Transformer\Custom\YardiSolanaVistaTransformer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\DataBundle\Entity\Import;

class YardiSolanaVistaTransformerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldCheckAddress()
    {
        $logger = $this->getLoggerMock();
        $em = $this->getEntityManagerMock();
        $fullResident = new FullResident();
        $fullResident->setResidentTransactionPropertyCustomer(new ResidentTransactionPropertyCustomer());
        $customers = new Customers();
        $customers->addCustomer($customer = new Customer());
        $fullResident->getResidentTransactionPropertyCustomer()->setUnit($unit = new ResidentTransactionUnit());
        $unit->setUnitId('Test');
        $fullResident->getResidentTransactionPropertyCustomer()->setCustomers($customers);
        $customer->setCustomerAddress(new CustomerAddress());
        $customer->getCustomerAddress()->setCustomerAddress1('Fishermans Dr');
        $property = new Property();
        $fullResident->setProperty($property);
        $transformer = new YardiSolanaVistaTransformer($em, $logger);
        $import = new Import();
        $import->setGroup(new Group());
        $transformer->transformData([$fullResident], $import);
        $this->assertCount(1, $import->getImportProperties());
        /** @var ImportProperty $importProperty */
        $importProperty = $import->getImportProperties()->get(0);
        $this->assertEquals('Test Fishermans Dr', $importProperty->getAddress1(), 'Address should map correct');
        $this->assertEmpty($importProperty->getUnitName(), 'Doesn\'t have unit name for that transformer');
    }
}
