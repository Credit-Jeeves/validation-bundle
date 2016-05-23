<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\CoreBundle\Command\EmailBatchDepositReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use DateTime;
use CreditJeeves\DataBundle\Entity\Group;

/**
 * @TODO: we need create new test for this and remove this test (user 3 loop and related with dynamic template)
 */
class EmailBatchDepositReportCommandCase extends BaseTestCase
{
    /**
     * @return array
     */
    public function provideSupportedAccountingSystem()
    {
        return [
            [AccountingSystem::PROMAS],
            [AccountingSystem::MRI_BOSTONPOST],
            [AccountingSystem::YARDI_GENESIS],
            [AccountingSystem::YARDI_GENESIS_2],
        ];
    }

    /**
     * @test
     * @dataProvider provideSupportedAccountingSystem
     */
    public function shouldSendEmailReportWithCsvAttachForHoldingAdmins($accountingSystem)
    {
        $this->load(true);
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->update('DataBundle:Holding', 'h')
            ->set('h.accountingSystem', $qb->expr()->literal($accountingSystem))
            ->getQuery()
            ->execute();

        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Landlord', 'l')
            ->set('l.culture', $qb->expr()->literal('test'))
            ->getQuery()
            ->execute();

        $date = new \DateTime();
        /**
         * Update date for all success transactions
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Transaction', 'h')
            ->set('h.depositDate', ':depositDate')
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('depositDate', $date)
            ->getQuery()
            ->execute();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommandTester(new EmailBatchDepositReportCommand(), ['--group-ids' => '24,25']);

        $this->assertCount(1, $plugin->getPreSendMessages(), 'Expected 1 mail with CSV attachment');
        $this->assertCount(
            2,
            $parts = $plugin->getPreSendMessage(0)->getChildren(),
            'Expected 2 parts: attachment and email body'
        );
        $this->assertInstanceOf(\Swift_Attachment::class, $parts[0], 'Expected attachment in email');
    }

    /**
     * @test
     * @dataProvider provideSupportedAccountingSystem
     */
    public function shouldSendOnceEmailReportWithCsvAttachForLandlordIsNotAdminIfSetGroupId($accountingSystem)
    {
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $landlord->setEmailNotification(true);
        $landlord->setIsSuperAdmin(false);
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->find(25);

        $landlord->addAgentGroup($group);

        $holding = $group->getHolding();
        $holding->setAccountingSystem($accountingSystem);

        $em->persist($holding);
        $em->persist($landlord);
        $em->flush();

        $date = new \DateTime();
        /**
         * Update date for all success transactions
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Transaction', 'h')
            ->set('h.depositDate', ':depositDate')
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('depositDate', $date)
            ->getQuery()
            ->execute();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommandTester(new EmailBatchDepositReportCommand(), ['--group-ids' => '25']);

        $this->assertCount(1, $plugin->getPreSendMessages(), 'Expected 1 mail with CSV attachment');
        $this->assertCount(
            2,
            $parts = $plugin->getPreSendMessage(0)->getChildren(),
            'Expected 2 parts: attachment and email body'
        );
        $this->assertInstanceOf(\Swift_Attachment::class, $parts[0], 'Expected attachment in email');
        /** @var \Swift_MimePart $mail */
        $mail = $parts[1];
        $this->assertTrue((bool) strpos($mail->getBody(), $group->getName()), 'The mail should include the group name');
    }

    /**
     * @test
     * @dataProvider provideSupportedAccountingSystem
     */
    public function shouldSendEmailReportWithCsvAttachForLandlordIsNotAdmin($accountingSystem)
    {
        $this->load(true);
        $em = $this->getEntityManager();

        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $landlord->setEmailNotification(true);
        $landlord->setIsSuperAdmin(false);

        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->find(25);
        $landlord->addAgentGroup($group);

        $holding = $group->getHolding();
        $holding->setAccountingSystem($accountingSystem);

        $em->persist($holding);
        $em->persist($landlord);
        $em->flush();

        $date = new \DateTime();
        /**
         * Update date for all success transactions
         */
        $qb = $em->createQueryBuilder();
        $qb->update('RjDataBundle:Transaction', 'h')
            ->set('h.depositDate', ':depositDate')
            ->where('h.batchId iS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('depositDate', $date)
            ->getQuery()
            ->execute();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommandTester(new EmailBatchDepositReportCommand());

        $this->assertCount(2, $plugin->getPreSendMessages(), 'Expected 2 mail with CSV attachment');
        $this->assertCount(
            2,
            $parts = $plugin->getPreSendMessage(0)->getChildren(),
            'Expected 2 parts: attachment and email body'
        );
        $this->assertInstanceOf(\Swift_Attachment::class, $parts[0], 'Expected attachment in email');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
