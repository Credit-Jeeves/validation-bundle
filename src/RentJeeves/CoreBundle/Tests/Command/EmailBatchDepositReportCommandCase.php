<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailBatchDepositReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use DateTime, DomDocument, DomXPath;


class EmailBatchDepositReportCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeReport()
    {
        $this->load(true);

        /**
         * First update not valid culture
         */
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /**
         * @var $qb \Doctrine\DBAL\Query\QueryBuilder
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Landlord', 'l')
            ->set('l.culture', $qb->expr()->literal('test'))->getQuery()->execute();

        /**
         * Update date for all success transactions
         */
        $qb->update('RjDataBundle:HeartLand', 'h')
            ->set('deposit_date', new DateTime("today"))
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1');

        $application = new Application($this->getKernel());
        $application->add(new EmailBatchDepositReportCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Email:batchDeposit:report');
        $commandTester = new CommandTester($command);
        $commandTester->execute([ 'command' => $command->getName() ]);

        $this->assertRegExp('/Start prepare daily batch deposit report by/', $commandTester->getDisplay());
        $this->assertCount(6, $plugin->getPreSendMessages());
    }
}
