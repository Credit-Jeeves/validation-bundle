<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\ExternalApiBundle\Command\AMSICloseBatchCommand;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\TestBundle\Command\BaseTestCase;

class AMSICloseBatchCommandCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateJobNotifier()
    {
        $this->load(true);
        /** @var Holding $holding */
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        $today = new \DateTime();
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update('DataBundle:Order', 'o')
            ->set('o.created_at', ':today')
            ->setParameter(':today', $today->format('Y-m-d H:i:s'))
            ->getQuery()
            ->execute();
        //Prepea failure job
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(22);

        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);
        $order->setDescriptor('Test Check');
        $order->setCreatedAt(new \DateTime());

        $operation = new Operation();
        $operation->setAmount(600);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setGroup(null);
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $order->addOperation($operation);
        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);

        $jobRelatedToOrder = new JobRelatedOrder();
        $jobRelatedToOrder->setOrder($order);
        $jobRelatedToOrder->setCreatedAt(new \DateTime());
        $job = new Job('external_api:payment:push');
        $job->addRelatedEntity($jobRelatedToOrder);
        $job->setState(Job::STATE_RUNNING);
        $job->setState(Job::STATE_FAILED);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->persist($jobRelatedToOrder);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        //end

        $soapClientFactory = $this->getBaseMock('\RentJeeves\ExternalApiBundle\Soap\SoapClientFactory');
        $amsiLedgerClient = $this->getBaseMock('\RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient');

        $amsiLedgerClient->expects($this->any())
            ->method('updateSettlementData')
            ->will($this->returnValue(true));

        $soapClientFactory->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($amsiLedgerClient));
        $this->getContainer()->set('soap.client.factory', $soapClientFactory);
        $application = new Application($this->getKernel());
        $syncCommand = new AMSICloseBatchCommand();

        $syncCommand->setContainer($this->getContainer());
        $application->add($syncCommand);

        $command = $application->find('api:accounting:amsi:close-batches');
        $originalFixtureJobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'renttrack:notify:batch-close-failure']
        );
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );
        $this->getEntityManager()->clear();
        $afterTestJobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'renttrack:notify:batch-close-failure']
        );

        $this->assertEquals(
            count($originalFixtureJobs)+1,
            count($afterTestJobs),
            'Should be added new job about notify failure close batch'
        );
    }
}

