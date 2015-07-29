<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\MigrateActivePaymentsCommand;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class MigrateActivePaymentsCommandCase extends BaseTestCase
{
    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @return CommandTester
     */
    protected function getCommandTester()
    {
        if (is_null($this->commandTester)) {
            $this->commandTester = new CommandTester($this->getCommand());
        }

        return $this->commandTester;
    }

    /**
     * @return Command
     */
    protected function getCommand()
    {
        if (is_null($this->command)) {
            $application = new Application($this->getKernel());
            $application->add(new MigrateActivePaymentsCommand());

            $this->command = $application->find('payment-processor:migrate-active-payments');
        }

        return $this->command;
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please, specify holding id!
     */
    public function shouldThrowExceptionIfHoldingIdIsNotPassed()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName()
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Holding with id #999 not found.
     */
    public function shouldThrowExceptionIfPassedNonExistentHoldingId()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 999
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified payment processor 'UNKNOWN' to migrate from is not found.
     */
    public function shouldThrowExceptionIfPassedInvalidPaymentProcessorFrom()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
                '--from-processor' => 'UNKNOWN'
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified payment processor 'UNKNOWN' to migrate to is not found.
     */
    public function shouldThrowExceptionIfPassedInvalidPaymentProcessorTo()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
                '--to-processor' => 'UNKNOWN'
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specified payment processors TO and FROM are identical.
     */
    public function shouldThrowExceptionIfPaymentProcessorFromAndPaymentProcessorToAreIdentical()
    {
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
                '--from-processor' => 'aci',
                '--to-processor' => 'aci'
            ]
        );
    }

    /**
     * @test
     */
    public function shouldNotCloseAnyPaymentsOnDefaultDBFixturesDueToLackOfAciPaymentAccounts()
    {
        $this->load(true);
        $holding = $this->getEntityManager()->find('DataBundle:Holding', 5);
        $activePaymentsHPS = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(5, $activePaymentsHPS);
        $activePaymentsACI = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(0, $activePaymentsACI);
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
            ]
        );
        $activePaymentsHPS = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(5, $activePaymentsHPS);
        $activePaymentsACI = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(0, $activePaymentsACI);
    }

    /**
     * @test
     */
    public function shouldCloseHPSActivePaymentAndOpenACIActivePayment()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $holding = $em->find('DataBundle:Holding', 5);
        $activePaymentsHPS = $em->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(5, $activePaymentsHPS);
        $activePaymentsACI = $em->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(0, $activePaymentsACI);

        /** @var Payment $hpsActivePayment */
        $hpsActivePayment = $activePaymentsHPS[0];

        $aciPaymentAccount = new PaymentAccount();
        $aciPaymentAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $aciPaymentAccount->setUser($hpsActivePayment->getPaymentAccount()->getUser());
        $aciPaymentAccount->setType($hpsActivePayment->getPaymentAccount()->getType());
        $aciPaymentAccount->setName('New ACI PA');
        $aciPaymentAccount->setToken('12345');

        $aciDepositAccount = new DepositAccount();
        $aciDepositAccount->setGroup($hpsActivePayment->getDepositAccount()->getGroup());
        $aciDepositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $aciDepositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $aciDepositAccount->setType(DepositAccountType::RENT);

        $em->persist($aciPaymentAccount);
        $em->persist($aciDepositAccount);
        $em->flush();

        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
            ]
        );
        $activePaymentsHPS = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(4, $activePaymentsHPS);
        $activePaymentsACI = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(1, $activePaymentsACI);

        /** @var Payment $aciActivePayment */
        $aciActivePayment = $activePaymentsACI[0];
        $this->assertEquals($hpsActivePayment->getContract(), $aciActivePayment->getContract());
        $this->assertEquals($hpsActivePayment->getAmount(), $aciActivePayment->getAmount());
        $this->assertEquals($hpsActivePayment->getTotal(), $aciActivePayment->getTotal());
        $this->assertEquals($hpsActivePayment->getPaidFor(), $aciActivePayment->getPaidFor());
        $this->assertEquals($hpsActivePayment->getDueDate(), $aciActivePayment->getDueDate());
        $this->assertEquals($hpsActivePayment->getType(), $aciActivePayment->getType());
        $this->assertEquals($hpsActivePayment->getStartMonth(), $aciActivePayment->getStartMonth());
        $this->assertEquals($hpsActivePayment->getStartYear(), $aciActivePayment->getStartYear());
        $this->assertEquals($hpsActivePayment->getEndMonth(), $aciActivePayment->getEndMonth());
        $this->assertEquals($hpsActivePayment->getEndYear(), $aciActivePayment->getEndYear());
    }

    /**
     * @test
     */
    public function shouldMigrateBackFromACIToHPS()
    {
        $holding = $this->getEntityManager()->find('DataBundle:Holding', 5);
        $activePaymentsHPS = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(4, $activePaymentsHPS);
        $activePaymentsACI = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(1, $activePaymentsACI);
        $this->getCommandTester()->execute(
            [
                'command' => $this->getCommand()->getName(),
                '--holding-id' => 5,
                '--from-processor' => 'aci',
                '--to-processor' => 'heartland'
            ]
        );
        $activePaymentsHPS = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::HEARTLAND);
        $this->assertCount(5, $activePaymentsHPS);
        $activePaymentsACI = $this->getEntityManager()->getRepository('RjDataBundle:Payment')
            ->getActiveHoldingPaymentsWithPaymentProcessor($holding, PaymentProcessor::ACI);
        $this->assertCount(0, $activePaymentsACI);
    }
}
