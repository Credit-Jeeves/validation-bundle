<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\RegisterContractToProfitStarsCommand;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorProfitStarsRdc;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ContractRegistryManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsRegisteredContract;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\ProfitStars\Mocks\PaymentVaultClientMock;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\RegisterCustomerResponse;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\ReturnValue;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\WSUpdateResult;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RegisterContractToProfitStarsCommandCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Contract with id#999 not found
     */
    public function shouldThrowExceptionIfContractNotFoundByContractIdArgument()
    {
        $this->load(true);
        $command = new RegisterContractToProfitStarsCommand();
        $this->executeCommandTester(
            $command,
            [
                'contract-id' => 999,
                'deposit-account-id' => 1
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage DepositAccount with id#987 not found
     */
    public function shouldThrowExceptionIfDepositAccountNotFoundByDepositAccountIdArgument()
    {
        $this->load(true);
        $command = new RegisterContractToProfitStarsCommand();
        $this->executeCommandTester(
            $command,
            [
                'contract-id' => 1,
                'deposit-account-id' => 987
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can't register contract #2. Holding "Rent Holding" has no ProfitStars merchantId set
     */
    public function shouldThrowExceptionIfHoldingDoesNotHaveProfitStarsSettings()
    {
        $this->load(true);
        $command = new RegisterContractToProfitStarsCommand();
        $this->executeCommandTester(
            $command,
            [
                'contract-id' => 2,
                'deposit-account-id' => 1
            ]
        );
    }

    /**
     * @test
     */
    public function shouldRegisterContractToProfitStarsForGivenDepositAccount()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Contract $contract */
        $contract = $em->find('RjDataBundle:Contract', 2);
        $this->assertNotNull($contract, 'Contract #2 should exist');
        $holding = $contract->getHolding();
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);

        $depositAccount = new DepositAccount();
        $depositAccount->setHolding($holding);
        $depositAccount->setGroup($contract->getGroup());
        $depositAccount->setMerchantName(1023318);
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $em->persist($depositAccount);

        $em->flush();

        $application = new Application($this->getKernel());
        $command = new RegisterContractToProfitStarsCommand();
        $command->setContainer($this->getContainerMock());
        $application->add($command);

        $testCommand = $application->find('renttrack:payment-processor:profit-stars:register-contract');

        $commandTester = new CommandTester($testCommand);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'contract-id' => $contract->getId(),
                'deposit-account-id' => $depositAccount->getId()
            ]
        );

        $registeredContracts = $contract->getProfitStarsRegisteredContracts();
        $this->assertCount(1, $registeredContracts, 'We expect 1 contract to be registered');
        /** @var ProfitStarsRegisteredContract $registeredContract */
        $registeredContract = $registeredContracts->first();
        $this->assertEquals(1023318, $registeredContract->getLocationId(), 'Contract should be registered to 1023318');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainerMock()
    {
        $clientMock = PaymentVaultClientMock::getMockForRegisterCustomer();

        $contractRegistry = new ContractRegistryManager(
            $clientMock,
            $this->getEntityManager(),
            $this->getContainer()->get('logger'),
            $this->getContainer()->get('skip32.id_encoder'),
            'test',
            'test'
        );

        $reportLoader = $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ReportLoader',
            [],
            [],
            '',
            false
        );

        $paymentProcessor = new PaymentProcessorProfitStarsRdc($reportLoader, $contractRegistry);
        $this->getContainer()->set('payment_processor.profit_stars.rdc', $paymentProcessor);

        return $this->getContainer();
    }
}
