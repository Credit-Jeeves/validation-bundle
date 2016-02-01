<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\CheckoutBundle\Command\RegisterContractsToProfitStarsCommand;
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
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\ReturnValue;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RegisterContractsToProfitStarsCommandCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage DepositAccount with id#111 not found
     */
    public function shouldThrowExceptionIfDepositAccountNotFoundByDepositAccountIdArgument()
    {
        $this->load(true);
        $command = new RegisterContractsToProfitStarsCommand();
        $this->executeCommandTester(
            $command,
            [
                'deposit-account-id' => 111,
                'page' => 1,
                'limit' => 1,
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfHoldingDoesNotHaveProfitStarsSettings()
    {
        $this->load(true);
        $command = new RegisterContractsToProfitStarsCommand();
        $this->executeCommandTester(
            $command,
            [
                'deposit-account-id' => 1,
                'page' => 1,
                'limit' => 1,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldRegisterContractsOfTheGivenPageToProfitStarsForGivenDepositAccount()
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
        $command = new RegisterContractsToProfitStarsCommand();
        $command->setContainer($this->getContainerMock());
        $application->add($command);

        $testCommand = $application->find('renttrack:payment-processor:profit-stars:register-contracts');

        $commandTester = new CommandTester($testCommand);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'deposit-account-id' => $depositAccount->getId(),
                'page' => 1,
                'limit' => 3,
            ]
        );

        $registeredContracts = $em->getRepository('RjDataBundle:ProfitStarsRegisteredContract')->findAll();
        $this->assertCount(3, $registeredContracts, 'We expect 3 contracts to be registered');
        /** @var ProfitStarsRegisteredContract $registeredContract */
        $registeredContract = $registeredContracts[0];
        $this->assertEquals(1023318, $registeredContract->getLocationId(), 'Contract should be registered to 1023318');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainerMock()
    {
        $clientMock = PaymentVaultClientMock::getMockForRegisterCustomer(ReturnValue::SUCCESS, 3);

        $contractRegistry = new ContractRegistryManager(
            $clientMock,
            $this->getEntityManager(),
            $this->getContainer()->get('logger'),
            $this->getContainer()->get('skip32.id_encoder'),
            'test',
            'test'
        );

        $reportLoader = $this->getBaseMock('\RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ReportLoader');

        $paymentProcessor = new PaymentProcessorProfitStarsRdc($reportLoader, $contractRegistry);
        $this->getContainer()->set('payment_processor.profit_stars.rdc', $paymentProcessor);

        return $this->getContainer();
    }
}
