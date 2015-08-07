<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\Command\MapAciImportProfileCommand;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MapAciImportProfileCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldMapAciProfileData()
    {
        $this->load(true);
        $em = $this->getEntityManager();

        $em->createQuery('DELETE RjDataBundle:AciImportProfileMap')->execute();
        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(0, $mapData, 'Map data should be empty');

        $billingAccounts = $em->getRepository('RjDataBundle:BillingAccount')
            ->createQueryBuilder('ba')
            ->groupBy('ba.group')
            ->getQuery()
            ->execute();

        $this->assertCount(0, $billingAccounts);

        $paymentAccounts = $em->getRepository('RjDataBundle:PaymentAccount')
            ->createQueryBuilder('pa')
            ->groupBy('pa.user')
            ->getQuery()
            ->execute();

        $this->assertCount(5, $paymentAccounts);

        $this->executeCommand();

        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(5, $mapData, 'Map data should have 5 records');
    }

    /**
     * @test
     * @depends shouldMapAciProfileData
     */
    public function shouldNotDuplicateAlreadyExistingProfileRecords()
    {
        $em = $this->getEntityManager();
        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(5, $mapData, 'Map data should have 5 records after shouldMapAciProfileData');

        $this->executeCommand();

        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(5, $mapData, 'Map data should not duplicate records');
    }

    /**
     * @test
     * @depends shouldMapAciProfileData
     */
    public function shouldAddNewRecordsIfNewBillingAccountAppears()
    {
        $em = $this->getEntityManager();
        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(5, $mapData, 'Map data should have 5 records after shouldMapAciProfileData');

        $billingAccounts = $em->getRepository('RjDataBundle:BillingAccount')
            ->createQueryBuilder('ba')
            ->groupBy('ba.group')
            ->getQuery()
            ->execute();

        $this->assertCount(0, $billingAccounts);

        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $billingAccount = new BillingAccount();
        $billingAccount->setGroup($group);
        $billingAccount->setToken('111111');
        $billingAccount->setNickname('billing nickname');
        $billingAccount->setBankAccountType(BankAccountType::CHECKING);
        $group->addBillingAccount($billingAccount);
        $em->persist($group);
        $em->flush($group);

        $billingAccounts = $em->getRepository('RjDataBundle:BillingAccount')
            ->createQueryBuilder('ba')
            ->groupBy('ba.group')
            ->getQuery()
            ->execute();

        $this->assertCount(1, $billingAccounts);

        $this->executeCommand();

        $mapData = $em->getRepository('RjDataBundle:AciImportProfileMap')->findAll();
        $this->assertCount(6, $mapData, 'Map data should add one new record');
    }

    protected function executeCommand()
    {
        $application = new Application($this->getKernel());
        $application->add(new MapAciImportProfileCommand());

        $command = $application->find('aci:import-profile:map');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
