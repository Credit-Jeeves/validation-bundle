<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Command\AMSICloseBatchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\TestBundle\Command\BaseTestCase;

class AMSICloseBatchCommandCase extends BaseTestCase
{
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
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $soapClientFactory = $this->getMock(
            '\RentJeeves\ExternalApiBundle\Soap\SoapClientFactory',
            [],
            [],
            '',
            false
        );
        $amsiLedgerClient = $this->getMock(
            '\RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient',
            [],
            [],
            '',
            false
        );

        $amsiLedgerClient->expects($this->any())
            ->method('updateSettlementData')
            ->will($this->returnValue(false));

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

