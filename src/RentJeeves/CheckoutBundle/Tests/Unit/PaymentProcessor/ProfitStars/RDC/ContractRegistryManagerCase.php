<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\DBALException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ContractRegistryManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsRegisteredContract;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\TestBundle\ProfitStars\Mocks\PaymentVaultClientMock;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\RegisterCustomerResponse;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\ReturnValue;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\WSUpdateResult;

class ContractRegistryManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldUseEncoderToGenerateCustomerNumberByEncodingContractId()
    {
        $encoderMock = $this->getEncoderMock();
        $encoderMock
            ->expects($this->once())
            ->method('encode')
            ->with('testID')
            ->will($this->returnValue('abc123'));

        $registryManager = new ContractRegistryManager(
            $this->getProfitStarsClientMock(),
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $encoderMock,
            '',
            ''
        );
        $contract = new Contract();
        $this->writeIdAttribute($contract, 'testID');
        $customerNumber = $registryManager->getCustomerNumber($contract);
        $this->assertEquals('abc123', $customerNumber, 'CustomerNumber should be "abc123"');
    }

    /**
     * @test
     */
    public function shouldCallLoggerDebugIfContractIsAlreadyRegisteredForGivenLocationId()
    {
        $loggerMock = $this->getLoggerMock();
        $loggerMock
            ->expects($this->once())
            ->method('debug');

        $registryManager = new ContractRegistryManager(
            $this->getProfitStarsClientMock(),
            $this->getEntityManagerMock(),
            $loggerMock,
            $this->getEncoderMock(),
            '',
            ''
        );
        $merchantName = 'testLocation';
        $contract = new Contract();
        $registeredContract = new ProfitStarsRegisteredContract();
        $registeredContract->setContract($contract);
        $registeredContract->setLocationId($merchantName);
        $contract->addProfitStarsRegisteredContract($registeredContract);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName($merchantName);

        $registryManager->registerContract($contract, $depositAccount);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException
     */
    public function shouldThrowPaymentProcessorRuntimeExceptionIfSavingContractThrowsDBALException()
    {
        $clientMock = PaymentVaultClientMock::getMockForRegisterCustomer();

        $emMock = $this->getEntityManagerMock();
        $emMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(new DBALException());

        $registryManager = new ContractRegistryManager(
            $clientMock,
            $emMock,
            $this->getLoggerMock(),
            $this->getEncoderMock(),
            '',
            ''
        );

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $tenant = new Tenant();
        $unit = new Unit();
        $holding = new Holding();
        $holding->setProfitStarsSettings(new ProfitStarsSettings());

        $contract = new Contract();
        $contract->setProperty($property);
        $contract->setTenant($tenant);
        $contract->setUnit($unit);
        $contract->setHolding($holding);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('testLocationTEST');
        $depositAccount->setHolding($holding);

        $registryManager->registerContract($contract, $depositAccount);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException
     */
    public function shouldThrowPaymentProcessorRuntimeExceptionIfProfitStarsClientReturnsFailedResult()
    {
        $clientMock = PaymentVaultClientMock::getMockForRegisterCustomer(ReturnValue::ERROR_UNKNOWN);

        $registryManager = new ContractRegistryManager(
            $clientMock,
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getEncoderMock(),
            '',
            ''
        );

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $tenant = new Tenant();
        $unit = new Unit();
        $holding = new Holding();
        $holding->setProfitStarsSettings(new ProfitStarsSettings());

        $contract = new Contract();
        $contract->setProperty($property);
        $contract->setTenant($tenant);
        $contract->setUnit($unit);
        $contract->setHolding($holding);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('testLocationTEST');
        $depositAccount->setHolding($holding);

        $registryManager->registerContract($contract, $depositAccount);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException
     */
    public function shouldThrowPaymentProcessorRuntimeExceptionIfProfitStarsClientThrowsException()
    {
        $clientResponse = new RegisterCustomerResponse();
        $clientResult = new WSUpdateResult();
        $clientResult->setReturnValue(ReturnValue::ERROR_UNKNOWN);
        $clientResponse->setRegisterCustomerResult($clientResult);

        $clientMock = $this->getProfitStarsClientMock();
        $clientMock
            ->expects($this->once())
            ->method('RegisterCustomer')
            ->willThrowException(new \Exception());

        $registryManager = new ContractRegistryManager(
            $clientMock,
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getEncoderMock(),
            '',
            ''
        );

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $tenant = new Tenant();
        $unit = new Unit();
        $holding = new Holding();
        $holding->setProfitStarsSettings(new ProfitStarsSettings());

        $contract = new Contract();
        $contract->setProperty($property);
        $contract->setTenant($tenant);
        $contract->setUnit($unit);
        $contract->setHolding($holding);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('testLocationTEST');
        $depositAccount->setHolding($holding);

        $registryManager->registerContract($contract, $depositAccount);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyRegisterContractIfItWasNotRegisteredBefore()
    {
        $clientMock = PaymentVaultClientMock::getMockForRegisterCustomer();

        $emMock = $this->getEntityManagerMock();
        $emMock
            ->expects($this->once())
            ->method('persist');

        $emMock
            ->expects($this->once())
            ->method('flush');

        $registryManager = new ContractRegistryManager(
            $clientMock,
            $emMock,
            $this->getLoggerMock(),
            $this->getEncoderMock(),
            '',
            ''
        );

        $propertyAddress = new PropertyAddress();
        $property = new Property();
        $property->setPropertyAddress($propertyAddress);

        $tenant = new Tenant();
        $unit = new Unit();
        $holding = new Holding();
        $holding->setProfitStarsSettings(new ProfitStarsSettings());

        $contract = new Contract();
        $contract->setProperty($property);
        $contract->setTenant($tenant);
        $contract->setUnit($unit);
        $contract->setHolding($holding);

        $depositAccount = new DepositAccount();
        $depositAccount->setMerchantName('testLocationTEST');
        $depositAccount->setHolding($holding);

        $registryManager->registerContract($contract, $depositAccount);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProfitStarsClientMock()
    {
        return $this->getBaseMock('RentTrack\ProfitStarsClientBundle\PaymentVault\Model\PaymentVaultClient');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEncoderMock()
    {
        return $this->getBaseMock('RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder');
    }
}
