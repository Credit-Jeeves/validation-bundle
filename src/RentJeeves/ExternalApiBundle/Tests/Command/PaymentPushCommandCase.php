<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Command\PaymentPushCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\DataBundle\Tests\Traits\TransactionTrait;

class PaymentPushCommandCase extends BaseTestCase
{
    use TransactionTrait;

    /**
     * @test
     */
    public function shouldSendPaymentToResMan()
    {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $transaction = $this->createTransaction();

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(1, $jobs);

        $job = reset($jobs);

        $application = new Application($this->getKernel());
        $application->add(new PaymentPushCommand());

        $command = $application->find('external_api:payment:push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'       => $command->getName(),
                '--jms-job-id'  => $job->getId(),
            ]
        );
        $this->assertRegExp("/Start\nSuccess/", $commandTester->getDisplay());
        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertTrue(
            $repo->isOpenedBatch(
                $transaction->getBatchId(),
                ApiIntegrationType::RESMAN,
                ResManClientCase::EXTERNAL_PROPERTY_ID
            )
        );
    }
}
