<?php
namespace RentJeeves\CoreBundle\Tests\Command;

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

        $date = new DateTime('today');
        /**
         * Update date for all success transactions
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Heartland', 'h')
            ->set('h.depositDate', ':today')
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('today', $date)
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
        $this->assertCount(6, $plugin->getPreSendMessages());
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($emails = $this->page->findAll('css', 'a'));
        $this->assertCount(6, $emails, 'Wrong number of emails');
        $emails[4]->click();
        $this->page->clickLink('text/html');

        $query = $em->getRepository('RjDataBundle:Heartland')->createQueryBuilder('h');
        $query->select("h.batchId");
        $query->groupBy('h.batchId');
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');

        $query->andWhere('h.depositDate = DATE(:date)');
        $query->setParameter('date', $date);

        $query->andWhere('h.batchId IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        /** Now we select only completed transaction */
        $query->andWhere('o.status = :status');
        $query->setParameter('status', OrderStatus::COMPLETE);

        $batches = $query->getQuery()->getScalarResult();

        $count = count($batches);

        $batchesEmail = $this->page->findAll('css', '.batch-id');

        $this->assertCount($count, $batchesEmail);

        for ($i= 0; $i < $count; $i++) {
            $this->assertEquals($batchesEmail[$i]->getText(), $batches[$i]['batchId']);
        }
    }
}
