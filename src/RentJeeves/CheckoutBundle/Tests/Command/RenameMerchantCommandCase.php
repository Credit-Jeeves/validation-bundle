<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\RenameMerchantCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class RenameMerchantCommandCase extends BaseTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    protected function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->em->beginTransaction();
    }

    /**
     * Rollback changes.
     */
    public function tearDown()
    {
        $this->em->rollback();
    }

    /**
     * @test
     */
    public function renameCase()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new RenameMerchantCommand());

        $oldMerchantName = 'Monticeto_Percent';
        $newMerchantName = 'RentTrackCorp';

        $depositAccountRepository = $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository('RjDataBundle:DepositAccount');

        $oldDepositAccount = $depositAccountRepository
            ->findOneByMerchantName($oldMerchantName);
        $group = $oldDepositAccount->getGroup();

        $paymentAccounts = $oldDepositAccount->getPaymentAccounts();

        // there's at least 1 payment account for the test
        $paymentAccountCount = $paymentAccounts->count();
        $this->assertGreaterThan(0, $paymentAccountCount);

        // new deposit account doesn't exist
        $this->assertEquals(
            null,
            $depositAccountRepository->findOneByMerchantName($newMerchantName)
        );

        $command = $application->find('rj:merchant:rename');
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'from' => $oldMerchantName,
                'to' => $newMerchantName
            )
        );

        $newDepositAccount = $depositAccountRepository
            ->findOneByMerchantName($newMerchantName);

        // new deposit account exists now
        $this->assertGreaterThan(0, $newDepositAccount->getId());

        // all the payment accounts are associated to the new deposit account
        foreach ($paymentAccounts as $paymentAccount) {
            $this->assertTrue(
                $paymentAccount
                    ->getDepositAccounts()
                    ->contains($newDepositAccount)
            );
        }

        // new deposit account has the group
        $this->assertEquals(
            $newDepositAccount,
            $group->getDepositAccount()
        );
    }
}
