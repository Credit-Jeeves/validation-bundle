<?php
namespace RentJeeves\CheckoutBundle\Tests\Functional\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\TransferPaymentAccountsCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class TransferPaymentAccountsCommandCase extends BaseTestCase
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
    public function registerToNewDepositAccountCase()
    {
        $this->markTestSkipped('Reenable this test as a part of RT-1720');
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new TransferPaymentAccountsCommand());

        $oldMerchantName = 'Monticeto_Percent';
        $newMerchantName = 'WestPac';

        $depositAccountRepository = $this
            ->getContainer()
            ->get('doctrine')
            ->getRepository('RjDataBundle:DepositAccount');

        $oldDepositAccount = $depositAccountRepository
            ->findOneByMerchantName($oldMerchantName);
        $newDepositAccount = $depositAccountRepository
            ->findOneByMerchantName($newMerchantName);

        $paymentAccounts = $oldDepositAccount->getPaymentAccounts();

        // there's at least 1 payment account for the test
        $paymentAccountCount = $paymentAccounts->count();
        $this->assertGreaterThan(0, $paymentAccountCount);

        $command = $application->find('rj:transfer_payment_accounts');
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'from' => $oldDepositAccount->getId(),
                'to' => $newDepositAccount->getId()
            )
        );

        // all the payment accounts are associated to the new deposit account
        foreach ($paymentAccounts as $paymentAccount) {
            $this->assertTrue(
                $paymentAccount
                    ->getDepositAccounts()
                    ->contains($newDepositAccount)
            );
        }
    }
}
