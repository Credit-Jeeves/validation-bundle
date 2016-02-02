<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\TestBundle\BaseTestCase as Base;

class ContractListenerCase extends Base
{
    /**
     * @expectedException \Symfony\Component\Form\Exception\LogicException
     * @test
     */
    public function makeSureIntegratedFieldWillNotChangeFromTrueToFalse()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $groupSetting GroupSettings
         */
        $groupSetting = $em->getRepository('RjDataBundle:GroupSettings')->findOneBy(
            array(
                'isIntegrated' => true
            )
        );
        $this->assertNotNull($groupSetting);
        $groupSetting->setIsIntegrated(false);
        $em->persist($groupSetting);
        $em->flush();
    }

    /**
     * @test
     */
    public function monitoringContractAmount()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Payment $payment */
        $payment = $em->getRepository('RjDataBundle:Payment')->findOneBy(
            array(
                'status' => PaymentStatus::ACTIVE,
                'amount' => 1400
            )
        );
        $paymentId = $payment->getId();
        $contract = $payment->getContract();
        $contract->setRent($payment->getAmount() + 100);
        $em->persist($contract);
        $em->flush();

        $this->assertCount(1, $plugin->getPreSendMessages());

        $this->assertEquals(
            'Your Rent amount was adjusted on your contract',
            $plugin->getPreSendMessage(0)->getSubject()
        );

        static::$kernel = null;
        $payment = $this->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository('RjDataBundle:Payment')
            ->find($paymentId);

        // RT-908 we should no longer close payments if rent changes
        $this->assertEquals(PaymentStatus::ACTIVE, $payment->getStatus());
    }

    /**
     * @test
     */
    public function shouldSendPaymentEmail()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                "email" => 'tenant11@example.com',
            )
        );
        /**
         * @var $contract Contract
         */
        $contract = $tenant->getContracts()->first();
        $this->assertEquals($contract->getPaymentAccepted(), PaymentAccepted::ANY);

        $contract->setPaymentAccepted(PaymentAccepted::DO_NOT_ACCEPT);
        $em->flush($contract);
        $this->assertCount(1, $message = $plugin->getPreSendMessages());
        $this->assertEquals('Online Payments Disabled', $message[0]->getSubject());

        $contract->setPaymentAccepted(PaymentAccepted::ANY);
        $em->flush($contract);
        $this->assertCount(2, $message = $plugin->getPreSendMessages());
        $this->assertEquals('Online Payments Enabled', $message[1]->getSubject());

        $contract->setPaymentAccepted(PaymentAccepted::CASH_EQUIVALENT);
        $em->flush($contract);
        $this->assertCount(3, $message = $plugin->getPreSendMessages());
        $this->assertEquals('Online Payments Disabled', $message[2]->getSubject());
    }

    /**
     * @test
     */
    public function shouldClosePayment()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                "email" => 'tenant11@example.com',
            )
        );
        /**
         * @var $contract Contract
         */
        $contract = $tenant->getContracts()[1];
        $contract->setPaymentAccepted(PaymentAccepted::DO_NOT_ACCEPT);
        $activePayment = $contract->getActiveRentPayment();
        $this->assertNotNull($activePayment);
        $em->flush($contract);
        $em->refresh($contract);
        $activePayment = $contract->getActiveRentPayment();
        $this->assertNull($activePayment);
    }

    /**
     * @test
     */
    public function shouldCreateNewJobToRegisterContractInProfitStarsIfNewContractCreatedWithValidDepositAccount()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Group $group */
        $group = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group, 'Group #24 should exist');
        $holding = $group->getHolding();
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);

        $depositAccount = new DepositAccount();
        $depositAccount->setHolding($holding);
        $depositAccount->setGroup($group);
        $depositAccount->setMerchantName(1023318);
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $group->addDepositAccount($depositAccount);
        $em->persist($depositAccount);

        $depositAccount2 = new DepositAccount();
        $depositAccount2->setHolding($holding);
        $depositAccount2->setGroup($group);
        $depositAccount2->setMerchantName(1023318);
        $depositAccount2->setType(DepositAccountType::APPLICATION_FEE);
        $depositAccount2->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount2->setStatus(DepositAccountStatus::DA_COMPLETE);
        $group->addDepositAccount($depositAccount2);
        $em->persist($depositAccount2);
        $em->flush();

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(4, $jobs, 'Should exist 4 jobs in the fixtures');

        $contract = new Contract();
        $contract->setGroup($group);
        $contract->setHolding($holding);
        /** @var Tenant $tenant */
        $tenant = $em->find('RjDataBundle:Tenant', 42);
        $this->assertNotNull($tenant, 'Tenant #42 should exist');
        $contract->setTenant($tenant);
        $contract->setRent(100);
        $contract->setUnit($tenant->getContracts()->first()->getUnit());
        $contract->setProperty($tenant->getContracts()->first()->getProperty());
        $contract->setStatus(ContractStatus::APPROVED);
        $em->persist($contract);

        $em->flush();
        $em->clear();

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(6, $jobs, 'Should exist 6 jobs: +2 new jobs for 2 ProfitStars deposit accounts');
        $this->assertNotEmpty($jobs[4], 'Job[4] should exist');
        $this->assertEquals('renttrack:payment-processor:profit-stars:register-contract', $jobs[4]->getCommand());
        $this->assertNotEmpty($jobs[5], 'Job[5] should exist');
        $this->assertEquals('renttrack:payment-processor:profit-stars:register-contract', $jobs[5]->getCommand());
    }

    /**
     * @test
     */
    public function shouldNotCreateNewJobToRegisterContractInProfitStarsIfNewContractCreatedWithInvalidDepositAccount()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Group $group */
        $group = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group, 'Group #24 should exist');
        $holding = $group->getHolding();
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);

        $contract = new Contract();
        $contract->setGroup($group);
        $contract->setHolding($holding);
        /** @var Tenant $tenant */
        $tenant = $em->find('RjDataBundle:Tenant', 42);
        $this->assertNotNull($tenant, 'Tenant #42 should exist');
        $contract->setTenant($tenant);
        $contract->setRent(100);
        $contract->setUnit($tenant->getContracts()->first()->getUnit());
        $contract->setProperty($tenant->getContracts()->first()->getProperty());
        $contract->setStatus(ContractStatus::APPROVED);
        $em->persist($contract);

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobs, 'Should exist 2 jobs in the fixtures');

        $em->flush();
        $em->clear();

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobs, 'Should exist 2 jobs (the same as before creating a contract)');
    }
}
