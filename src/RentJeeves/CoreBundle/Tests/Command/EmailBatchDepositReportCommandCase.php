<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Behat\Mink\Element\NodeElement;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CoreBundle\Command\EmailBatchDepositReportCommand;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use DateTime;

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
            ->set('l.culture', $qb->expr()->literal('test'))
            ->getQuery()
            ->execute();

        $date = new DateTime('-1 day');
        /**
         * Update date for all success transactions
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Heartland', 'h')
            ->set('h.depositDate', ':yesterday')
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('yesterday', $date)
            ->getQuery()
            ->execute();

        $application = new Application($this->getKernel());
        $application->add(new EmailBatchDepositReportCommand());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Email:batchDeposit:report');
        $commandTester = new CommandTester($command);
        $commandTester->execute([ 'command' => $command->getName() ]);

        $this->assertRegExp('/Start prepare daily batch deposit report by/', $commandTester->getDisplay());
        $this->assertCount(1, $plugin->getPreSendMessages());
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($emails = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $emails[0]->click(); // Holding Admin Email
        $this->page->clickLink('text/html');

        $groupNames = $this->page->findAll('css', '.group-name');

        $query = $em->getRepository('RjDataBundle:Heartland')->createQueryBuilder('h');
        $query->select("h.batchId");
        $query->groupBy('g.id'); // first group by Group, because one bathId can be included to diff group
        $query->addGroupBy('h.batchId'); // after that be batchId remove duplicate
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.group', 'g');

        $query->where('g.name IN (:groupNames)');
        $groupNames = array_map(
            function (NodeElement $value) {
                return $value->getText();
            },
            $groupNames
        );

        $query->setParameter('groupNames', $groupNames);

        $query->andWhere('h.depositDate = DATE(:date)');
        $query->setParameter('date', $date);

        $query->andWhere('h.batchId IS NOT NULL');
        $query->andWhere('h.transactionId IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        $query->andWhere('o.status in (:status)');
        $query->setParameter('status', [OrderStatus::COMPLETE, OrderStatus::REFUNDED, OrderStatus::RETURNED]);

        $batches = array_map(
            function ($value) {
                return $value['batchId'];
            },
            $query->getQuery()->getScalarResult()
        );

        $count = count($batches);

        $batchesEmail = $this->page->findAll('css', '.batch-id');

        $this->assertCount($count, $batchesEmail);

        for ($i= 0; $i < $count; $i++) {
            $this->assertTrue(in_array($batchesEmail[$i]->getText(), $batches));
        }
    }
}
