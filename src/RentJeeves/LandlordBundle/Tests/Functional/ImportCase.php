<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\DataBundle\Model\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\CoreBundle\DateTime;

class ImportCase extends ImportBaseAbstract
{

    /**
     * @test
     */
    public function shouldGetMappingForImport()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $group = $em->getRepository('DataBundle:Group')->findOneBy(
            array(
                'name' => 'Test Rent Group'
            )
        );
        $externalUnitId = 'AAABBB-7';
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->getMappingForImport(
            $group,
            $externalUnitId
        );

        $this->assertNotNull($unitMapping);
    }

    protected function setPropertySecond()
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'street' => 'Broadway',
                'number' => '785',
                'zip' => '10003'
            ]
        );
        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());
    }


    /**
     * @test
     */
    public function alreadyHaveAccount()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();

        $this->waitReviewAndPost(false);
        $this->assertNotNull($invite = $this->page->find('css', '.0_sendInvite'));
        $invite->check();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();

        $this->waitReviewAndPost(false);

        $this->waitRedirectToSummaryPage();
        $this->logout();
        //Check notify tenant invite for new user
        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#payRentLink')->getNode(0)->getAttribute('href');

        $this->session->visit($url);
        $this->assertNotNull($haveAccount = $this->page->find('css', '.haveAccount>a'));
        $haveAccount->click();

        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($payButtons = $this->page->findAll('css', '.button-contract-pay'));
        $this->assertCount(5, $payButtons);
        $this->logout();

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            array(
                'rent' => 777666.00,
                'integratedBalance' => 1277.00
            )
        );

        $this->assertNotNull($contract);
        $this->assertEquals('tenant11@example.com', $contract->getTenant()->getEmail());
    }

    /**
     * @test
     * @depends alreadyHaveAccount
     */
    public function checkMatchedUser()
    {
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
    }

    /**
     * @test
     */
    public function matchWaitingContract()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_RESIDENT_ID,
            '2' => ImportMapping::KEY_TENANT_NAME,
            '3' => ImportMapping::KEY_RENT,
            '4' => ImportMapping::KEY_BALANCE,
            '5' => ImportMapping::KEY_UNIT_ID,
            '6' => ImportMapping::KEY_STREET,
            '7' => ImportMapping::KEY_CITY,
            '8' => ImportMapping::KEY_STATE,
            '9' => ImportMapping::KEY_ZIP,
            '10' => ImportMapping::KEY_MOVE_IN,
            '11' => ImportMapping::KEY_LEASE_END,
            '12' => ImportMapping::KEY_MOVE_OUT,
            '14' => ImportMapping::KEY_EMAIL,
        );
        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertNotNull($firstName1 = $this->page->find('css', 'input.0_first_name'));
        $firstName1->setValue('Logan');
        $this->assertNotNull($lastName1 = $this->page->find('css', 'input.0_last_name'));
        $lastName1->setValue('Cooper');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        //after that check mathced status
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('duplicate_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
    }

    /**
     * @return array
     */
    public function providerForMatchWaitingContractWithMoveContract()
    {
        return [
            [new DateTime(), 100.01],
            [new DateTime('+1 month'), -10.02]
        ];
    }

    /**
     * @test
     * @dataProvider providerForMatchWaitingContractWithMoveContract
     */
    public function matchWaitingContractWithMoveContract(\DateTime $paidToIn, $balanceIn)
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_MOVE_IN,
            '6' => ImportMapping::KEY_LEASE_END,
            '7' => ImportMapping::KEY_MOVE_OUT,
            '8' => ImportMapping::KEY_BALANCE,
            '9' => ImportMapping::KEY_EMAIL,
        );
        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.new']), "New contract is wrong number");
        $this->assertNotNull($email = $this->page->find('css', 'input.0_email'));
        $email->setValue('');
        $this->assertNotNull($balance = $this->page->find('css', 'input.0_balance'));
        $balance->setValue($balanceIn);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        //after that check mathced status
        $this->page->clickLink('tab.accounting');

        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('one_user_for_waiting_room.csv');
        $attFile->attachFile($filePath);
        $this->setPropertyFirst();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_MOVE_IN,
            '6' => ImportMapping::KEY_LEASE_END,
            '7' => ImportMapping::KEY_MOVE_OUT,
            '8' => ImportMapping::KEY_BALANCE,
            '9' => ImportMapping::KEY_EMAIL,
        );
        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.match']), "Match contract is wrong number");
        $this->assertNotNull($balance = $this->page->find('css', 'input.0_balance'));
        $balance->setValue($balanceIn);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();

        $em = $this->getEntityManager();
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            ['integratedBalance' => $balanceIn]
        );
        $this->assertEquals(1, count($contracts));
        /** @var Contract $contract */
        $contract = end($contracts);
        $dueDate = $contract->getGroup()->getGroupSettings()->getDueDate();
        $this->assertEquals($dueDate, $contract->getDueDate());
        $paidToIn->setDate(null, null, $contract->getDueDate());
        $this->assertEquals($paidToIn->format('Y-m-d'), $contract->getPaidTo()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function duplicateResidentIdShouldBeSkippedWithError()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_two_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(1, count($errorFields));
        $this->assertEquals($errorFields[0]->getHtml(), '15test@mail.com');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitRedirectToSummaryPage();
        $em = $this->getEntityManager();
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'rent' => 777666,
            )
        );
        $this->assertEquals(1, count($contracts));
        $this->logout();

    }

    /**
     * @test
     */
    public function skippedMessageAndInfoDateInvalid()
    {
        $this->load(true);
        // get count of property
        /** @var PropertyRepository $repo */
        $repo = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Property');
        $count = $repo->createQueryBuilder('p')->select('count(p.id)')->getQuery()->getSingleScalarResult();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('skipped_message_and_date_notice.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_PROPERTIES);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = array(
            '1' => ImportMapping::KEY_RESIDENT_ID,
            '2' => ImportMapping::KEY_TENANT_NAME,
            '3' => ImportMapping::KEY_RENT,
            '4' => ImportMapping::KEY_BALANCE,
            '5' => ImportMapping::KEY_UNIT_ID,
            '6' => ImportMapping::KEY_STREET,
            '7' => ImportMapping::KEY_CITY,
            '8' => ImportMapping::KEY_STATE,
            '9' => ImportMapping::KEY_ZIP,
            '10' => ImportMapping::KEY_MOVE_IN,
            '11' => ImportMapping::KEY_LEASE_END,
            '12' => ImportMapping::KEY_MOVE_OUT,
            '14' => ImportMapping::KEY_EMAIL,
        );
        $this->fillCsvMapping($mapFile, 15);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.waiting']), "Waiting contract is wrong number");
        $this->assertEquals(1, count($trs['import.status.skip']), "Skip contract is wrong number");

        $this->assertNotNull($info = $this->page->find('css', '.information-box'));
        $this->assertEquals('import.error.mapping_date', trim($info->getHtml()));
        $this->assertNotNull($td = $this->page->find('css', '.line_number_1 td'));
        $this->assertEquals('import.info.skipped2', trim($td->getAttribute('title')));
        // check that added 2 new properties from import file
        $countNew = $repo->createQueryBuilder('p')->select('count(p.id)')->getQuery()->getSingleScalarResult();
        $this->assertEquals($countNew, $count + 2);
    }

    /**
     * @test
     */
    public function setsMatchingFieldsForImport()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');

        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $submitImportFile->click();
        // Choose mapping fields
        $this->assertNotNull($table = $this->page->find('css', 'table'));
        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column' . $i));
            $this->assertEquals('empty_value', $choice->getValue());
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table#importTable'));
        $this->logout();
        // Check that mapping is saved to DB
        $importMappingChoice = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:ImportMappingChoice')->findAll();
        $this->assertTrue(count($importMappingChoice) > 0);

        // Do import again w/o mapping
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $filePath = $this->getFilePathByName('import.csv');
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $submitImportFile->click();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table#importTable'));
        $this->logout();
    }

    /**
     * @test
     */
    public function importMultipleGroups()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");

        //First Step
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::MULTI_GROUPS);
        $filePath = $this->getFilePathByName('import_multiple_group.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/y');
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        foreach ($this->mapMultipleGroupFile as $i => $choiceOption) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column' . $i));
            $choice->selectOption($choiceOption);
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->assertNull($errorFields = $this->page->find('css', 'input.errorField'));

        $trs = $this->getParsedTrsByStatus();

        $this->assertCount(2, $trs, "Count statuses is wrong");
        $this->assertCount(
            4,
            $trs['import.status.skip'],
            "One contract should be skipped, because we don't have such account number and 3 isn't integrated"
        );

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

        $submitImportFile->click();

        $this->waitReviewAndPost(false);
        $this->waitRedirectToSummaryPage();
        $em = $this->getEntityManager();

        $unitMapping = $em
            ->getRepository('RjDataBundle:UnitMapping')
            ->findOneBy(['externalUnitId' => 'SO1004-M']);

        $this->assertNotNull($unitMapping);
        /** @var \RentJeeves\DataBundle\Entity\Unit $unit */
        $this->assertNotNull($unit = $unitMapping->getUnit());
        // We sure that only one contract for this unit was created
        $this->assertNotNull($contract = $unit->getContracts()->first());
        $this->assertEquals('Test Rent Group', $contract->getGroup()->getName());
    }

    /**
     * @test
     */
    public function shouldOnlyNewAndException()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_only_exception.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        $submitImportFile->click();

        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();

        $this->session->wait(
            15000,
            "$('.errorField').length > 0"
        );

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(1, count($errorFields));
        $this->assertEquals($errorFields[0]->getValue(), '2testmail.com');

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(1, count($trs), "Count statuses is wrong");
        $this->assertEquals(1, count($trs['import.status.new']), "New contract on first page is wrong number");
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->waitRedirectToSummaryPage();
    }

    /**
     * @test
     */
    public function shouldThrowExceptionForImportSinglePropertyWithoutUnit()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Property $property */
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'street' => 'Broadway',
                'number' => '785',
                'zip' => '10003'
            ]
        );
        /*
         * Create a property with no units
         */
        $units = $property->getUnits();
        foreach ($units as $unit) {
            $em->remove($unit);
        }
        $em->flush();

        $propertyAddress = $property->getPropertyAddress();
        $propertyAddress->setIsSingle(true);

        $em->persist($property);
        $em->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_one_user.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));

        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());

        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');

        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost(false);

        $this->assertNotNull($informationBox = $this->page->find('css', '.information-box>span'));
        $this->assertEquals('import.description_exception', $informationBox->getHtml());
        $this->assertNotNull($skipException = $this->page->find('css', '.skipException'));
        $skipException->click();
        $this->waitReviewAndPost(false);

        $this->waitRedirectToSummaryPage();
    }

    /**
     * @test
     */
    public function shouldImportPromasExtraField()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('promas_extra_field.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($importTypeSelected = $this->page->find('css', '#import_file_type_importType'));
        $importTypeSelected->selectOption(ImportType::SINGLE_PROPERTY);
        $this->setPropertyFirst();
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        //Second Step
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = [
            '1' => ImportMapping::KEY_UNIT,
            '2' => ImportMapping::KEY_RESIDENT_ID,
            '3' => ImportMapping::KEY_TENANT_NAME,
            '4' => ImportMapping::KEY_RENT,
            '5' => ImportMapping::KEY_BALANCE,
            '6' => ImportMapping::KEY_MOVE_IN,
            '7' => ImportMapping::KEY_LEASE_END,
            '8' => ImportMapping::KEY_MOVE_OUT,
            '9' => ImportMapping::KEY_EMAIL,
            '10' => ImportMapping::KEY_USER_PHONE,
            '11' => ImportMapping::KEY_CREDITS,
            '13' => ImportMapping::KEY_PAYMENT_ACCEPTED,
        ];

        $this->fillCsvMapping($mapFile, 13);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount(1, $trs, "Count statuses is wrong");
        $this->assertCount(3, $trs['import.status.new'], "Count of new contracts is wrong");

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $em = $this->getEntityManager();
        /** @var Tenant $userWithPhone */
        $userWithPhone = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('user_phone@mail.com');
        /** @var Tenant $userWithOpenCredits */
        $userWithOpenCredits = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('open_credits@mail.com');
        /** @var Tenant $userWithPaymentAccepted */
        $userWithPaymentAccepted = $em->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('payment_accepted@mail.com');

        $this->assertNotNull($userWithPhone);
        $this->assertNotNull($userWithOpenCredits);
        $this->assertNotNull($userWithPaymentAccepted);

        $this->assertEquals('0978822205', $userWithPhone->getPhone());

        $contractPhoneUser = $userWithPhone->getContracts()->first();
        $this->assertNotEmpty($contractPhoneUser);
        $this->assertEquals(
            PaymentAccepted::DO_NOT_ACCEPT,
            $contractPhoneUser->getPaymentAccepted()
        );
        $contractOpenCredits = $userWithOpenCredits->getContracts()->first();
        $this->assertNotEmpty($contractOpenCredits);
        $this->assertEquals(
            PaymentAccepted::DO_NOT_ACCEPT,
            $contractOpenCredits->getPaymentAccepted()
        );
        $contractPaymentAccepted = $userWithPaymentAccepted->getContracts()->first();
        $this->assertNotEmpty($contractPaymentAccepted);
        $this->assertEquals(
            PaymentAccepted::ANY,
            $contractPaymentAccepted->getPaymentAccepted()
        );

        $this->assertEquals(
            200,
            $contractOpenCredits->getIntegratedBalance()
        );
    }

    /**
     * @test
     */
    public function shouldCreateContractFromWaitingOnOnlyNewAndException()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $beforeWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $beforeContracts = $em->getRepository('RjDataBundle:Contract')->findAll();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->setPropertyFirst();
        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_waiting_only_exception.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();
        $submitImportFile->click();

        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $this->fillCsvMapping($this->mapFile, 14);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $trs = $this->getParsedTrsByStatus();
        $this->assertEquals(2, count($trs), "Count statuses is wrong");
        $submitImportFile->click();
        $this->waitReviewAndPost(false);

        $this->waitRedirectToSummaryPage();

        $afterWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $afterContracts = $em->getRepository('RjDataBundle:Contract')->findAll();

        $this->assertCount(count($beforeWaiting) - 1, $afterWaiting);
        $this->assertCount(count($beforeContracts) + 1, $afterContracts);
    }

    /**
     * @test
     */
    public function mriBaseImport()
    {
        $this->markTestSkipped('Temporarily skip this test due to: need actual contract with filled address field');
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $em = $this->getEntityManager();
        /** @var $landlord Landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $holding = $landlord->getHolding();
        $holding->setApiIntegrationType(ApiIntegrationType::MRI);
        $em->flush($holding);
        // We must make sure the data saved into DB, so we count before import and after
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(23, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(1, $contractWaiting);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->setPropertySecond();
        $this->assertNotNull($source = $this->page->findAll('css', '.radio'));
        $source[1]->click();
        $this->assertNotNull($propertyId = $this->page->find('css', '#import_file_type_propertyId'));
        $propertyId->setValue('500');
        $submitImport->click();

        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        for ($i = 0; $i <= 2; $i++) {
            $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));

            if ($i === 0) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(2, $errorFields);
                $errorFields[0]->setValue('06/01/2008');
                $errorFields[1]->setValue('06/01/2008');
            }

            if ($i === 1 || $i === 2) {
                $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
                $this->assertCount(3, $errorFields);
                $errorFields[0]->setValue('06/01/2008');
                $errorFields[1]->setValue('06/01/2008');
                $errorFields[2]->setValue('06/01/2008');
            }
            $submitImportFile->click();
            $this->waitReviewAndPost();
        }

        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(25, $contract);
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findAll();
        $this->assertCount(10, $contractWaiting);
    }

    /**
     * @test
     */
    public function shouldImportCurrentPromasTenantsAndSetMonthToMonthToTrueIfMonthToMonthIsNo()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');

        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_current_tenant.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $this->setPropertyFirst();
        $this->assertNotNull($dateSelector = $this->page->find('css', '.import-date'));
        $dateSelector->selectOption('m/d/Y');
        $submitImportFile->click();
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;
        $mapFile[16] = ImportMapping::KEY_MONTH_TO_MONTH;

        $this->fillCsvMapping($mapFile, 16);

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(1000, "$('table').is(':visible')");

        $importContractStatuses = $this->getParsedTrsByStatus();

        $this->assertCount(1, $importContractStatuses, '1 contract status should be found: "new"');
        $this->assertCount(2, $importContractStatuses['import.status.new'], '2 new contracts should be imported');

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $this->assertNotNull($publicId = $this->page->find('css', '#publicId'));
    }
}
