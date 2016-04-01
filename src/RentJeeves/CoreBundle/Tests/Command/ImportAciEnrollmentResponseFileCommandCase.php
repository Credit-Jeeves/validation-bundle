<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\ImportAciEnrollmentResponseFileCommand;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class ImportAciEnrollmentResponseFileCommandCase extends BaseTestCase
{
    /**
     * @return string
     */
    protected function getFilePath()
    {
        return $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfSendNotCorrectHoldingId()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new ImportAciEnrollmentResponseFileCommand());

        $command = $application->find('payment-processor:aci-import:import-enrollment-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $this->getFilePath(),
                '--holding_id' => 123456,
            ]
        );
    }

    /**
     * @test
     */
    public function shouldImportDataFromFileWithCorrectHolding()
    {
        $this->load(true);

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
        //Consumer record
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(0, $allAciUserProfiles);
        //Account record
        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(0, $allAciCollectPayProfileBillings);
        // Funding record
        $allPaymentAccounts = $this->getPaymentAccountRepository()->findAll();
        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(0, $allPaymentMigrations);

        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new ImportAciEnrollmentResponseFileCommand());

        $command = $application->find('payment-processor:aci-import:import-enrollment-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $filePath,
                '--holding_id' => 5,
            ]
        );
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(1, $allAciUserProfiles);
        $this->assertEquals(1, $allAciUserProfiles[0]->getProfileId());

        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(1, $allAciCollectPayProfileBillings);
        $this->assertEquals(123, $allAciCollectPayProfileBillings[0]->getDivisionId());

        $allPaymentAccountsBeforeExecute = $this->getPaymentAccountRepository()->findAll();
        $this->assertEquals(count($allPaymentAccounts) + 1, count($allPaymentAccountsBeforeExecute));

        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(1, $allPaymentMigrations);

        $this->assertEquals('', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function shouldImportDataFromFileWithNotCorrectHolding()
    {
        $this->load(true);

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
        //Consumer record
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(0, $allAciUserProfiles);
        //Account record
        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(0, $allAciCollectPayProfileBillings);
        // Funding record
        $allPaymentAccounts = $this->getPaymentAccountRepository()->findAll();
        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(0, $allPaymentMigrations);

        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new ImportAciEnrollmentResponseFileCommand());

        $command = $application->find('payment-processor:aci-import:import-enrollment-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $filePath,
                '--holding_id' => 4,
            ]
        );

        //Consumer record
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(0, $allAciUserProfiles);
        //Account record
        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(0, $allAciCollectPayProfileBillings);
        // Funding record
        $allPaymentAccountsBeforeExecute = $this->getPaymentAccountRepository()->findAll();
        $this->assertEquals(count($allPaymentAccounts), count($allPaymentAccountsBeforeExecute));

        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(0, $allPaymentMigrations);

        $this->assertContains(
            'AciImportProfileMap#1: ConsumerResponseRecord: contracts for Holding#4 and Tenant#42 not found',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'AciImportProfileMap#1: AccountResponseRecord: contracts for Holding#4 and Tenant#42 not found',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'AciImportProfileMap#1: FundingResponseRecord: contracts for Holding#4 and Tenant#42 not found',
            $commandTester->getDisplay()
        );
    }

    /**
     * @test
     */
    public function shouldImportDataFromFileWithoutHolding()
    {
        $this->load(true);

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
        //Consumer record
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(0, $allAciUserProfiles);
        //Account record
        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(0, $allAciCollectPayProfileBillings);
        // Funding record
        $allPaymentAccounts = $this->getPaymentAccountRepository()->findAll();
        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(0, $allPaymentMigrations);

        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new ImportAciEnrollmentResponseFileCommand());

        $command = $application->find('payment-processor:aci-import:import-enrollment-file');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--path' => $filePath
            ]
        );
        $allAciUserProfiles = $this->getAciCollectPayUserProfileRepository()->findAll();
        $this->assertCount(1, $allAciUserProfiles);
        $this->assertEquals(1, $allAciUserProfiles[0]->getProfileId());

        $allAciCollectPayProfileBillings = $this->getAciCollectPayProfileBillingRepository()->findAll();
        $this->assertCount(1, $allAciCollectPayProfileBillings);
        $this->assertEquals(123, $allAciCollectPayProfileBillings[0]->getDivisionId());

        $allPaymentAccountsBeforeExecute = $this->getPaymentAccountRepository()->findAll();
        $this->assertEquals(count($allPaymentAccounts) + 1, count($allPaymentAccountsBeforeExecute));

        $allPaymentMigrations = $this->getPaymentAccountMigrationRepository()->findAll();
        $this->assertCount(1, $allPaymentMigrations);

        $this->assertEquals('', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function shouldDisplayErrorsForEachRow()
    {
        $this->load(true);

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/AciEnrollmentResponseWithBadData.csv'
        );
        $this->executeCommandTester(
            new ImportAciEnrollmentResponseFileCommand(),
            [
                '--path' => $filePath,
                '--holding_id' => 5
            ]
        );

        $errorsString = $this->commandTester->getDisplay();
        $this->assertContains(
            'ConsumerResponseRecord#consumerProfileId : This value should not be blank.',
            $errorsString
        );
        $this->assertContains(
            'ConsumerResponseRecord#status : Unable to process the record with status \'R\'.',
            $errorsString
        );
        $this->assertContains(
            'ConsumerResponseRecord#rejectReason : Unable to process the record with rejectReason.',
            $errorsString
        );
        $this->assertContains(
            'AccountResponseRecord#billingAccountId : This value should not be blank.',
            $errorsString
        );
        $this->assertContains(
            'FundingResponseRecord#fundingAccountId : This value should not be blank.',
            $errorsString
        );
    }

    /**
     * @test
     */
    public function shouldNotCreateNewAddressForFundingAccountTypeIsBank()
    {
        $this->load(true);

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
        $allMailingAddress = $this->getEntityManager()->getRepository('DataBundle:MailingAddress')->findAll();
        $count = count($allMailingAddress);

        $this->executeCommandTester(
            new ImportAciEnrollmentResponseFileCommand(),
            [
                '--path' => $filePath,
                '--holding_id' => 5,
            ]
        );
        $allMailingAddress = $this->getEntityManager()->getRepository('DataBundle:MailingAddress')->findAll();
        $countAfterImport = count($allMailingAddress);

        $this->assertEquals($count, $countAfterImport, 'Address not should be created.');
    }

    /**
     * @test
     */
    public function shouldCreateNewAddressForFundingAccountTypeIsNotBankAndOldAccountDoesntHaveAddress()
    {
        $this->load(true);
        $paymentAccount = $this->getPaymentAccountRepository()->findOneOrNullByToken(
            '568C0904-9174-46DE-BEC4-9B76599B28C5'
        );
        $this->assertNotNull($paymentAccount, 'Pls check db.');
        $paymentAccount->setType(PaymentAccountType::CARD);
        $paymentAccount->setAddress(null);
        $this->getEntityManager()->flush();

        $filePath = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForCommand.csv'
        );
        $allMailingAddress = $this->getEntityManager()->getRepository('DataBundle:MailingAddress')->findAll();
        $count = count($allMailingAddress);

        $this->executeCommandTester(
            new ImportAciEnrollmentResponseFileCommand(),
            [
                '--path' => $filePath,
                '--holding_id' => 5,
            ]
        );
        $allMailingAddress = $this->getEntityManager()->getRepository('DataBundle:MailingAddress')->findAll();
        $countAfterImport = count($allMailingAddress);

        $this->assertEquals($count + 1, $countAfterImport, 'New address should be created.');
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAciCollectPayUserProfileRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:AciCollectPayUserProfile');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAciCollectPayProfileBillingRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:AciCollectPayProfileBilling');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PaymentAccountRepository
     */
    protected function getPaymentAccountRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPaymentAccountMigrationRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccountMigration');
    }
}
