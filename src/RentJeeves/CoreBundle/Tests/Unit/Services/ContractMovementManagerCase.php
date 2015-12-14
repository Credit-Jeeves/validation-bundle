<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\CoreBundle\Services\ContractMovementManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class ContractMovementManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateNewObjectContractMovement()
    {
        new ContractMovementManager(
            $this->getEntityManagerMock(),
            $this->getPaymentProcessorFactoryMock(),
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractMovementManagerException
     * @expectedExceptionMessage srcUnit#1 and dstUnit#2 are in different holdings
     */
    public function shouldLogErrorAndReturnFalseIfSrcUnitAndDstUnitHaveDifferentHolding()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding(new Holding());
        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding(new Holding());

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method($this->equalTo('warning'))
            ->with($this->stringContains('srcUnit#1 and dstUnit#2 are in different holdings.'));

        $contractMovement = new ContractMovementManager(
            $this->getEntityManagerMock(),
            $this->getPaymentProcessorFactoryMock(),
            $logger
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractMovementManagerException
     * @expectedExceptionMessage we cannot move contracts to groups that use a different payment processor.
     */
    public function shouldLogErrorAndReturnFalseIfSrcUnitAndDstUnitHaveDifferentPaymentProcessors()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding($holding = new Holding());

        $srcGroup = new Group();
        $srcGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $srcUnit->setGroup($srcGroup);

        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding($holding);

        $dstGroup = new Group();
        $dstGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $dstUnit->setGroup($dstGroup);

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method($this->equalTo('warning'))
            ->with($this->stringContains('we cannot move contracts to groups that use a different payment processor.'));

        $contractMovement = new ContractMovementManager(
            $this->getEntityManagerMock(),
            $this->getPaymentProcessorFactoryMock(),
            $logger
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractMovementManagerException
     * @expectedExceptionMessage resident ID#1 for Tenant#5 follow units. We must resolve manually first.
     */
    public function shouldLogErrorAndReturnFalseIfTenantHasExternalResidentIdAndGroupIsExternalResidentFollowsUnit()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding($holding = new Holding());

        $srcGroup = new Group();
        $srcGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $srcGroup->getGroupSettings()->setExternalResidentFollowsUnit(true);
        $srcUnit->setGroup($srcGroup);

        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding($holding);

        $dstGroup = new Group();
        $dstGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $dstUnit->setGroup($dstGroup);

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);
        $tenant = new Tenant();
        $this->writeIdAttribute($tenant, 5);
        $srcContract->setTenant($tenant);
        $srcContract->setHolding($holding);
        $srcContract->setGroup($srcGroup);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method($this->equalTo('warning'))
            ->with($this->stringContains('resident ID#1 for Tenant#5 follow units. We must resolve manually first.'));

        $residentMapping = new ResidentMapping();
        $residentMapping->setResidentId('1');

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue($residentMapping));

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:ResidentMapping'))
            ->will($this->returnValue($repositoryMock));

        $contractMovement = new ContractMovementManager(
            $em,
            $this->getPaymentProcessorFactoryMock(),
            $logger
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractMovementManagerException
     * @expectedExceptionMessage Can not update active Payment
     */
    public function shouldLogErrorAndReturnFalseIfCantUpdateActivePayments()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding($holding = new Holding());

        $srcGroup = new Group();
        $srcGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $srcUnit->setGroup($srcGroup);

        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding($holding);

        $dstGroup = new Group();
        $dstGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $dstUnit->setGroup($dstGroup);

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);
        $srcContract->setTenant(new Tenant());
        $srcContract->setHolding($holding);
        $srcContract->setGroup($srcGroup);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method($this->equalTo('warning'))
            ->with($this->stringContains('Can not update active Payment'));

        $residentMappingRepositoryMock = $this->getEntityRepositoryMock();
        $residentMappingRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue(new ResidentMapping()));

        $payment = new Payment();
        $payment->setDepositAccount($paymentDepositAccount = new DepositAccount());

        $paymentRepositoryMock = $this->getEntityRepositoryMock();
        $paymentRepositoryMock->expects($this->once())
            ->method($this->equalTo('findBy'))
            ->will($this->returnValue([$payment]));

        $depositAccountRepositoryMock = $this->getEntityRepositoryMock();
        $depositAccountRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue(null));

        $em = $this->getEntityManagerMock();
        $em->expects($this->at(0))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:ResidentMapping'))
            ->will($this->returnValue($residentMappingRepositoryMock));
        $em->expects($this->at(1))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Payment'))
            ->will($this->returnValue($paymentRepositoryMock));
        $em->expects($this->at(2))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:DepositAccount'))
            ->will($this->returnValue($depositAccountRepositoryMock));

        $factory = $this->getPaymentProcessorFactoryMock();
        $factory->expects($this->once())
            ->method($this->equalTo('getPaymentProcessor'))
            ->with($this->equalTo($dstGroup))
            ->will($this->returnValue(null));

        $contractMovement = new ContractMovementManager(
            $em,
            $factory,
            $logger
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractMovementManagerException
     * @expectedExceptionMessage Could not retokenize DepositAccount#1 : test
     */
    public function shouldLogErrorAndReturnFalseIfCantRetokenizeDepositAccount()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding($holding = new Holding());

        $srcGroup = new Group();
        $srcGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $srcUnit->setGroup($srcGroup);

        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding($holding);

        $dstGroup = new Group();
        $dstGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $dstUnit->setGroup($dstGroup);

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);
        $srcContract->setTenant(new Tenant());
        $srcContract->setHolding($holding);
        $srcContract->setGroup($srcGroup);

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method($this->equalTo('warning'))
            ->with($this->stringContains('Could not retokenize DepositAccount#1 : test'));

        $residentMappingRepositoryMock = $this->getEntityRepositoryMock();
        $residentMappingRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue(new ResidentMapping()));

        $payment = new Payment();
        $payment->setDepositAccount($paymentDepositAccount = new DepositAccount());

        $paymentRepositoryMock = $this->getEntityRepositoryMock();
        $paymentRepositoryMock->expects($this->once())
            ->method($this->equalTo('findBy'))
            ->will($this->returnValue([$payment]));

        $similarDepositAccount = new DepositAccount();
        $this->writeIdAttribute($similarDepositAccount, 1);

        $depositAccountRepositoryMock = $this->getEntityRepositoryMock();
        $depositAccountRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue($similarDepositAccount));

        $em = $this->getEntityManagerMock();
        $em->expects($this->at(0))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:ResidentMapping'))
            ->will($this->returnValue($residentMappingRepositoryMock));
        $em->expects($this->at(1))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Payment'))
            ->will($this->returnValue($paymentRepositoryMock));
        $em->expects($this->at(2))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:DepositAccount'))
            ->will($this->returnValue($depositAccountRepositoryMock));

        $paymentProcessorMock = $this->getPaymentProcessorAciCollectPayMock();
        $paymentProcessorMock->expects($this->once())
            ->method($this->equalTo('registerPaymentAccount'))
            ->will($this->throwException(new \Exception('test')));

        $factory = $this->getPaymentProcessorFactoryMock();
        $factory->expects($this->once())
            ->method($this->equalTo('getPaymentProcessor'))
            ->with($this->equalTo($dstGroup))
            ->will($this->returnValue($paymentProcessorMock));

        $contractMovement = new ContractMovementManager(
            $em,
            $factory,
            $logger
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfAllDataIsValid()
    {
        $srcUnit = new Unit();
        $this->writeIdAttribute($srcUnit, 1);
        $srcUnit->setHolding($holding = new Holding());

        $srcGroup = new Group();
        $srcGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $srcUnit->setGroup($srcGroup);

        $dstUnit = new Unit();
        $this->writeIdAttribute($dstUnit, 2);
        $dstUnit->setHolding($holding);

        $dstGroup = new Group();
        $dstGroup->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $dstUnit->setGroup($dstGroup);

        $srcContract = new Contract();
        $this->writeIdAttribute($srcContract, 1);
        $srcContract->setUnit($srcUnit);
        $srcContract->setTenant(new Tenant());
        $srcContract->setHolding($holding);
        $srcContract->setGroup($srcGroup);

        $residentMappingRepositoryMock = $this->getEntityRepositoryMock();
        $residentMappingRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue(new ResidentMapping()));

        $payment = new Payment();
        $payment->setDepositAccount($paymentDepositAccount = new DepositAccount());

        $paymentRepositoryMock = $this->getEntityRepositoryMock();
        $paymentRepositoryMock->expects($this->once())
            ->method($this->equalTo('findBy'))
            ->will($this->returnValue([$payment]));

        $similarDepositAccount = new DepositAccount();
        $this->writeIdAttribute($similarDepositAccount, 1);

        $depositAccountRepositoryMock = $this->getEntityRepositoryMock();
        $depositAccountRepositoryMock->expects($this->once())
            ->method($this->equalTo('findOneBy'))
            ->will($this->returnValue($similarDepositAccount));

        $em = $this->getEntityManagerMock();
        $em->expects($this->at(0))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:ResidentMapping'))
            ->will($this->returnValue($residentMappingRepositoryMock));
        $em->expects($this->at(1))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:Payment'))
            ->will($this->returnValue($paymentRepositoryMock));
        $em->expects($this->at(2))
            ->method($this->equalTo('getRepository'))
            ->with($this->equalTo('RjDataBundle:DepositAccount'))
            ->will($this->returnValue($depositAccountRepositoryMock));
        $em->expects($this->once())
            ->method($this->equalTo('flush'));

        $paymentProcessorMock = $this->getPaymentProcessorAciCollectPayMock();
        $paymentProcessorMock->expects($this->once())
            ->method($this->equalTo('registerPaymentAccount'))
            ->will($this->returnValue(true));

        $factory = $this->getPaymentProcessorFactoryMock();
        $factory->expects($this->once())
            ->method($this->equalTo('getPaymentProcessor'))
            ->with($this->equalTo($dstGroup))
            ->will($this->returnValue($paymentProcessorMock));

        $contractMovement = new ContractMovementManager(
            $em,
            $factory,
            $this->getLoggerMock()
        );

        $contractMovement->move($srcContract, $dstUnit);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentProcessorFactory
     */
    protected function getPaymentProcessorFactoryMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentProcessorAciCollectPay
     */
    protected function getPaymentProcessorAciCollectPayMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay',
            [],
            [],
            '',
            false
        );
    }
}
