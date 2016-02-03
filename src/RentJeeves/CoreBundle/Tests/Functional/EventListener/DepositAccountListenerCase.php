<?php

namespace RentJeeves\CoreBundle\Tests\Functional\EventListener;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class DepositAccountListenerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateJobsWithRegisterToProfitStarsCommandIfNewCompleteProfitStarsDACreated()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Holding $holding */
        $holding = $em->find('DataBundle:Holding', 5);
        $this->assertNotNull($holding, 'Holding #5 should exist');
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);

        $depositAccount = new DepositAccount();
        $depositAccount->setHolding($holding);
        /** @var Group $group */
        $group = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group, 'Group #24 should exist');
        $depositAccount->setGroup($group);
        $depositAccount->setMerchantName(1023318);
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $em->persist($depositAccount);

        $jobsBeforeFlush = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobsBeforeFlush, 'Expected 2 existing jobs before flush');

        $em->flush();

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(3, $jobs, 'Expected 3 jobs after flush: +1 for register to ProfitStars');
        $this->assertNotEmpty($jobs[2], 'Job[2] should exist');
        $this->assertEquals('renttrack:payment-processor:profit-stars:register-contracts', $jobs[2]->getCommand());
    }

    /**
     * @test
     */
    public function shouldCreateJobsWithRegisterToProfitStarsCommandIfExistingProfitStarsDAGoesToComplete()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Holding $holding */
        $holding = $em->find('DataBundle:Holding', 5);
        $this->assertNotNull($holding, 'Holding #5 should exist');
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);

        $depositAccount = $holding->getDepositAccounts()[0];
        $depositAccount->setMerchantName(1023318);
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_INIT);

        $jobsOrigin = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobsOrigin, 'Expected 2 existing jobs before setting DA to INIT');

        $em->flush(); // should not trigger any new jobs b/c DA status INIT

        $jobsBeforeComplete = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobsBeforeComplete, 'Expected 2 existing jobs after setting DA to INIT');

        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);

        $em->flush();

        $jobs = $em->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(3, $jobs, 'Expected 3 jobs after setting DA to Complete: +1 for register to ProfitStars');
        $this->assertNotEmpty($jobs[2], 'Job[2] should exist');
        $this->assertEquals('renttrack:payment-processor:profit-stars:register-contracts', $jobs[2]->getCommand());
    }
}
