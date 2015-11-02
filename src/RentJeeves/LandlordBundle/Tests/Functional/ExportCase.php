<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;
use \SimpleXMLElement;
use ZipArchive;

class ExportCase extends BaseTestCase
{

    protected function selectExportBy($exportBy)
    {
        $this->assertNotNull(
            $radioInputs = $this->page->findAll('css', '#base_order_report_type_export_by_box input[type=radio]')
        );
        $this->assertCount(2, $radioInputs);
        for ($i = 0; $i <= 1; $i++) {
            $radioInput = $radioInputs[$i];
            if ($radioInput->getAttribute('value') === $exportBy) {
                $radioInput->selectOption($exportBy);
            }
        }
    }

    protected function selectFirstProperty()
    {
        $em = $this->getEntityManager();
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy([
            'street' => 'Broadway',
            'number' => '770',
            'zip'    => '10003'
        ]);
        $this->assertNotNull($property);
        $this->assertNotNull($propertyInputSelect = $this->page->find('css', '#base_order_report_type_property'));
        $propertyInputSelect->selectOption($property->getId());
    }

    protected function createPayment()
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy([
            'email' => 'tenant11@example.com'
        ]);

        $group = $em->getRepository('DataBundle:Group')->findOneBy([
            'name' => 'Test Rent Group'
        ]);

        $property = $em->getRepository('RjDataBundle:Property')->findOneBy([
            'zip' => '10003',
            'number' => '770',
            'jb' => '40.7308364',
            'kb' => '-73.991567'
        ]);
        /** @var DepositAccount $depositAccount */
        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'accountNumber' => '15235678'
            ]
        );

        $this->assertNotEmpty($depositAccount);
        $this->assertNotNull($tenant);
        $this->assertNotNull($property);
        $this->assertNotNull($group);

        $order = new OrderSubmerchant();
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(999);
        $order->setUser($tenant);
        $order->setDepositAccount($depositAccount);
        $oneWeekAgo = new DateTime();
        $oneWeekAgo->modify("-7 days");
        $order->setCreatedAt($oneWeekAgo);
        $em->persist($order);
        $em->flush($order);

        /** @var UnitMapping $unitMapping */
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy(['externalUnitId' => 'AAABBB-7']);
        $this->assertNotNull($unitMapping);
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(
            [
                'tenant' => $tenant,
                'group' => $group,
                'unit'  => $unitMapping->getUnit()
            ]
        );

        $this->assertNotNull($contract);

        $operation = new Operation();
        $operation->setAmount(999);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setPaidFor(new DateTime('8/1/2014'));
        $operation->setContract($contract);
        $order->addOperation($operation);

        $transaction = new Transaction();
        $transaction->setIsSuccessful(false);
        $transaction->setOrder($order);
        $transaction->setTransactionId("1");
        $transaction->setAmount(999);
        $transaction->setMerchantName('MrchntNm');
        $order->addTransaction($transaction);

        $em->persist($order);
        $em->persist($operation);
        $em->persist($transaction);
        $em->flush();
    }

    public function goToYardiReport()
    {
        $this->load(true);
        //$this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');

        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(3, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($property = $this->page->find('css', '#base_order_report_type_property'));
        $this->assertNotNull($propertyId = $this->page->find('css', '#base_order_report_type_propertyId'));

        $propertyId->setValue(100);
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $property->selectOption(1);
        $this->selectFirstProperty();
    }

    /**
     * @test
     */
    public function baseXmlFormat()
    {
        $this->goToYardiReport();
        $this->page->pressButton('order.report.download');

        $xml = $this->page->getContent();
        $doc = new SimpleXMLElement($xml);

        $this->assertNotNull($receipts = $doc->Receipts);
        $this->assertNotNull($receipt = $receipts->Receipt);
        $this->assertNotNull($date = $receipt->Date);
        $this->assertNotNull($totalAmount = $receipt->TotalAmount);
        $this->assertNotNull($isCash = $receipt->IsCash);
        $this->assertNotNull($checkNumber = $receipt->CheckNumber);
        $this->assertNotNull($notes = $receipt->Notes);
        $this->assertNotNull($propertyId = $receipt->PropertyId);
        $this->assertNotNull($payerName = $receipt->PayerName);
        $this->assertNotNull($personId = $receipt->PersonId);
        $this->assertNotNull($postMonth = $receipt->PostMonth);

        $this->assertEquals(100, (int) $receipt->PropertyId);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 123123', (string) $checkNumber);
        $this->assertEquals('t0013534', (string) $personId);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string) $notes);
    }

    /**
     * @test
     */
    public function completeYardiXmlFormat()
    {
        $this->goToYardiReport();
        $this->selectFirstProperty();
        $dateStart = new DateTime('-45 days');
        $dateEnd = new DateTime();
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $begin->setValue($dateStart->format('m/d/Y'));
        $end->setValue($dateEnd->format('m/d/Y'));

        $this->page->pressButton('order.report.download');

        $xml = $this->page->getContent();
        $doc = new SimpleXMLElement($xml);

        $this->assertNotNull($receipts = $doc->Receipts);
        $this->assertNotNull($receipt = $receipts->Receipt);
        $this->assertNotNull($date = $receipt->Date);
        $this->assertNotNull($totalAmount = $receipt->TotalAmount);
        $this->assertNotNull($isCash = $receipt->IsCash);
        $this->assertNotNull($checkNumber = $receipt->CheckNumber);
        $this->assertNotNull($notes = $receipt->Notes);
        $this->assertNotNull($propertyId = $receipt->PropertyId);
        $this->assertNotNull($payerName = $receipt->PayerName);
        $this->assertNotNull($personId = $receipt->PersonId);
        $this->assertNotNull($postMonth = $receipt->PostMonth);

        $this->assertTrue(isset($receipt->BatchId));

        $this->assertEquals(100, (int) $receipt->PropertyId);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 456456', (string) $checkNumber);
        $this->assertEquals('t0013534', (string) $personId);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string) $notes);
    }

    public function exportByRealPageCsv()
    {
        return [
            ['deposits', 14],
            ['payments', 16],
        ];
    }

    /**
     * @test
     * @dataProvider exportByRealPageCsv
     */
    public function realPageCsvFormat($exportBy, $countRows)
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $this->selectExportBy($exportBy);
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('real_page');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(3, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($building = $this->page->find('css', '#base_order_report_type_buildingId'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $building->setValue(75);
        $this->selectFirstProperty();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $this->assertFalse(strpos($csv, '"'));
        $this->assertFalse(strpos($csv, '\''));
        $csvArr = explode("\n", $csv);
        $this->assertTrue(isset($csvArr[0]));

        $this->assertCount($countRows, $csvArr);

        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('75', $csvArr[0]);
        $this->assertEquals('2-a', $csvArr[1]);
        $this->assertEquals('1500.00', $csvArr[3]);
        $this->assertEquals('TIMOTHY', $csvArr[4]);
        $this->assertEquals('APPLEGATE', $csvArr[5]);
        $this->assertEquals('PMTCRED', $csvArr[6]);
        $this->assertEquals(123123, $csvArr[7]);
        $this->assertEquals('770 Broadway Manhattan New York NY 10003 #2-a BATCH# 125478', $csvArr[8]);
    }

    /**
     * @test
     */
    public function realPageBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('real_page');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(3, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($building = $this->page->find('css', '#base_order_report_type_buildingId'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $building->setValue(88);
        $makeZip->check();
        $this->selectFirstProperty();
        $this->page->pressButton('order.report.download');

        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(8, $archive->numFiles);
        $file = $archive->getFromIndex(1);
        $rows = explode("\n", trim($file));
        $this->assertEquals(2, count($rows));
        $columns = str_getcsv($rows[0]);
        $this->assertEquals(88, $columns[0]);
        $this->assertEquals('2-a', $columns[1]);
        $this->assertEquals(1800, $columns[3]);
        $this->assertEquals('770 Broadway Manhattan New York NY 10003 #2-a BATCH# 325698', $columns[8]);
    }

    public function exportByPromasCsv()
    {
        return [
            ['deposits', 14, 'uncheck'],
            ['payments', 16, 'uncheck'],
            ['deposits', 14, 'check'],
            ['payments', 16, 'check'],
        ];
    }

    /**
     * @test
     * @dataProvider exportByPromasCsv
     */
    public function promasCsvFormat($exportBy, $countRows, $methodForAllGroups)
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('promas');
        $this->selectExportBy($exportBy);
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->assertNotNull($forAllGroups = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $forAllGroups->$methodForAllGroups();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);

        $this->assertCount($countRows, $csvArr, 'Actual row count should equal to expected.');

        // check file with unit id
        $this->assertNotNull($csvArrRow = str_getcsv($csvArr[2]), 'Row #2 should exist');
        $this->assertEquals('AAABBB-7', $csvArrRow[1], 'External unit id should be AAABBB-7');
        $this->assertEquals('t0013534', $csvArrRow[4], 'Resident id should be t0013534');

        // check file without unit id
        $this->assertNotNull($csvArrRow2 = str_getcsv($csvArr[10]), 'Row #10 should exist');
        $this->assertEmpty($csvArrRow2[1], 'Unit should be empty: there is no external unit id.');
        $this->assertEquals('t0011981', $csvArrRow2[4], 'Resident id should be t0011981');
    }

    /**
     * @test
     */
    public function promasBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('promas');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');

        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(8, $archive->numFiles, 'Archive should have 8 files');

        // check file with unit id
        $file = $archive->getFromIndex(1);
        $rows = explode("\n", trim($file));
        $this->assertEquals(1, count($rows));
        $columns = explode(",", $rows[0]);
        $this->assertEquals('AAABBB-7', $columns[1], 'Unit id should be AAABBB-7');
        $this->assertEquals(1500, $columns[2], 'Amount should be 1500');
        $this->assertEquals($columns[3], '"Trans #123123 Batch #125478"');
        $this->assertEquals('t0013534', $columns[4], 'Resident id should be t0013534');

        // check file without unit id
        $file = $archive->getFromIndex(4);
        $rows = explode("\n", trim($file));
        $this->assertEquals(2, count($rows), 'File should have 2 rows');
        $columns = explode(",", $rows[0]);
        $this->assertEmpty($columns[1], 'Unit should be empty');
        $this->assertEquals(1250, $columns[2], 'Amount should be 1250');
        $this->assertContains('Batch #555000', $columns[3]);
        $this->assertEquals('t0011981', $columns[4], 'Resident id should be t0011981');
    }

    /**
     * @test
     */
    public function shouldNotIncludeTenantIdIfHoldingSettingIsOff()
    {
        $this->load(true);
        $this->createPayment();
        $em = $this->getEntityManager();
        /** @var Holding $holding */
        $this->assertNotNull($holding = $em->find('DataBundle:Holding', 5), 'Holding id#5 should exist');
        $holding->setExportTenantId(false);
        $em->flush($holding);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('promas');
        $this->selectExportBy('payments');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);

        $this->assertCount(16, $csvArr, 'Actual row count should equal to expected.');

        // check file with unit id
        $this->assertNotNull($csvArrRow = str_getcsv($csvArr[0]), 'Row should exist');
        $this->assertEquals('', $csvArrRow[4], 'Resident id should be empty');
    }

    /**
     * @test
     */
    public function yardiBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();

        $this->assertNotNull($propertyId = $this->page->find('css', '#base_order_report_type_propertyId'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));

        $propertyId->setValue(100);
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');
        $xmlsZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $xmlsZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(14, $archive->numFiles);
        $file = $archive->getFromIndex(1);

        $doc = new SimpleXMLElement($file);

        $this->assertNotNull($receipts = $doc->Receipts);
        $this->assertNotNull($receipt = $receipts->Receipt);
        $this->assertNotNull($date = $receipt->Date);
        $this->assertNotNull($totalAmount = $receipt->TotalAmount);
        $this->assertNotNull($personId = $receipt->PersonId);
        $this->assertNotNull($postMonth = $receipt->PostMonth);
        $this->assertNull($details = $receipt->Details->Detail);
        $this->assertNotNull($notes = $receipt->Notes);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('t0013534', (string) $personId);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string) $notes);
    }

    public function exportByRentTrackCsv()
    {
        return [
            ['deposits', 15],
            ['payments', 16],
        ];
    }

    /**
     * @test
     * @dataProvider exportByRentTrackCsv
     */
    public function rentTrackCsvFormat($exportBy, $countRows)
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('renttrack');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->selectFirstProperty();
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));

        $this->assertEquals($notice->getText(), 'export.no_data');

        $beginD->modify('-1 year');
        $begin->setValue($beginD->format('m/d/Y'));
        $this->selectExportBy($exportBy);
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();

        $csvFullArr = explode("\n", $csv);
        $this->assertCount($countRows, $csvFullArr);
        /** check Last */
        $this->assertNotNull($csvArr = str_getcsv($csvFullArr[9]));
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003', $csvArr[1]);
        $this->assertEquals('AAABBB-7', $csvArr[2]);
        $this->assertEquals('456456', $csvArr[7]);
        $this->assertEquals('325698', $csvArr[8]);
        $this->assertEquals('t0013534', $csvArr[4]);
        $this->assertEquals('15235678', $csvArr[13]);
        /** check Refunded */
        $this->assertNotNull($csvArr = str_getcsv($csvFullArr[11]));
        $this->assertEquals('-700.00', $csvArr[6]);
        $this->assertEquals('65123261', $csvArr[7]);
        $this->assertEquals('', $csvArr[8]);
    }

    /**
     * @test
     */
    public function rentTrackBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('renttrack');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');
        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(8, $archive->numFiles);
        $file = $archive->getFromIndex(2);
        $rows = explode("\n", trim($file));
        $this->assertEquals(3, count($rows));
        $columns = str_getcsv($rows[1]);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003', $columns[1]);
        $this->assertEquals('15235678', $columns[13]);
    }

    public function exportByYardiGenesisCsv()
    {
        return [
            ['deposits', 25, 'check'],
            ['payments', 26, 'check'],
            ['deposits', 14, 'uncheck'],
            ['payments', 16, 'uncheck'],
        ];
    }

    /**
     * @test
     * @dataProvider exportByYardiGenesisCsv
     */
    public function yardiGenesisCsvFormat($exportBy, $countRows, $methodForAllGroups)
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->selectExportBy($exportBy);
        $this->assertNotNull($forAllGroubs = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $this->selectFirstProperty();
        $forAllGroubs->$methodForAllGroups();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", trim($csv));
        $this->assertCount($countRows, $csvArr);

        $this->assertTrue(isset($csvArr[0]));
        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('123123', $csvArr[1]);
        $this->assertEquals('1500', $csvArr[3]);
        // $this->assertEquals('08/14/2014', $csvArr[4]);   // The Date seems to change with each build each day
        $this->assertEquals('770 Broadway, Manhattan #2-a 125478', $csvArr[5]);
    }

    public function exportByYardiGenesisV2Csv()
    {
        return [
            ['deposits', 14, 'uncheck'],
            ['payments', 16, 'uncheck'],
            ['deposits', 25, 'check'],
            ['payments', 26, 'check'],
        ];
    }

    /**
     * @test
     * @dataProvider exportByYardiGenesisV2Csv
     */
    public function yardiGenesisV2CsvFormat($exportBy, $countRows, $methodForAllGroups)
    {
        $this->load(true);
        $this->createPayment();
        //$this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis_v2');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));

        $this->exportByYardiGenesisV2Csv($exportBy);
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->selectExportBy($exportBy);
        $this->assertNotNull($forAllGroubs = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $forAllGroubs->$methodForAllGroups();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\r", trim($csv));
        $this->assertCount($countRows, $csvArr);
        $this->assertTrue(isset($csvArr[0]));

        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('123123', $csvArr[1]);
        $this->assertEquals('1500', $csvArr[3]);
        // $this->assertEquals('08/14/2014', $csvArr[4]);   // The Date seems to change with each build each day
        $this->assertEquals('770 Broadway, Manhattan #2-a 125478', $csvArr[5]);
        $this->assertEquals('', $csvArr[6]);
        $this->assertEquals('', $csvArr[7]);
        $this->assertEquals('', $csvArr[8]);
    }

    /**
     * @test
     */
    public function yardiGenesisBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');

        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(8, $archive->numFiles);
        $file = $archive->getFromIndex(1);
        $rows = explode("\n", trim($file));

        $this->assertEquals(2, count($rows));
        $csvArr = str_getcsv($rows[0]);
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('456456', $csvArr[1]);
        $this->assertEquals('1800', $csvArr[3]);
        // $this->assertEquals('08/24/2014', $csvArr[4]); // the Date seems to change with each build each day
        $this->assertEquals('770 Broadway, Manhattan #2-a 325698', $csvArr[5]);
    }

    /**
     * @test
     */
    public function yardiGenesisV2BatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis_v2');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');

        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(8, $archive->numFiles);
        $file = $archive->getFromIndex(1);
        $rows = explode("\r", trim($file));
        $this->assertCount(2, $rows);
        $csvArr = str_getcsv($rows[0]);
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('456456', $csvArr[1]);
        $this->assertEquals('1800', $csvArr[3]);
        // $this->assertEquals('08/24/2014', $csvArr[4]); // the Date seems to change with each build each day
        $this->assertEquals('770 Broadway, Manhattan #2-a 325698', $csvArr[5]);
        $this->assertEquals('', $csvArr[6]);
        $this->assertEquals('', $csvArr[7]);
        $this->assertEquals('', $csvArr[8]);
    }

    /**
     * @test
     */
    public function yardiGenesisV2CsvFormatForAllProperties()
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis_v2');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertCount(2, $errors);

        $this->exportByYardiGenesisV2Csv('payments');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->selectExportBy('payments');
        $this->assertNotNull($forAllGroubs = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $forAllGroubs->check();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\r", trim($csv));
        $this->assertCount(26, $csvArr);
        $this->assertTrue(isset($csvArr[0]));

        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('123123', $csvArr[1]);
        $this->assertEquals('1500', $csvArr[3]);
        $this->assertEquals('770 Broadway, Manhattan #2-a 125478', $csvArr[5]);
        $this->assertEquals('', $csvArr[6]);
        $this->assertEquals('', $csvArr[7]);
        $this->assertEquals('', $csvArr[8]);
    }

    /**
     * @test
     */
    public function yardiGenesisCsvFormatForAllProperties()
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('yardi_genesis');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->selectFirstProperty();
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $this->selectExportBy('payments');
        $this->assertNotNull($forAllGroubs = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $forAllGroubs->check();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", trim($csv));
        $this->assertCount(26, $csvArr);

        $this->assertTrue(isset($csvArr[0]));
        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('R', $csvArr[0]);
        $this->assertEquals('123123', $csvArr[1]);
        $this->assertEquals('1500', $csvArr[3]);
        $this->assertEquals('770 Broadway, Manhattan #2-a 125478', $csvArr[5]);
    }

    /**
     * @test
     */
    public function realPageCsvFormatForAllProperties()
    {
        $this->load(true);
        $this->createPayment();
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.export');
        $this->selectExportBy('deposits');
        $beginD = new DateTime();
        $beginD->modify('-1 year');
        $endD = new DateTime();

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('real_page');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(3, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($building = $this->page->find('css', '#base_order_report_type_buildingId'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $building->setValue(75);
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $this->assertFalse(strpos($csv, '"'));
        $this->assertFalse(strpos($csv, '\''));
        $csvArr = explode("\n", $csv);
        $this->assertTrue(isset($csvArr[0]));

        $this->assertCount(14, $csvArr);

        $this->assertNotNull($csvArr = str_getcsv($csvArr[0]));
        $this->assertEquals('75', $csvArr[0]);
        $this->assertEquals('2-a', $csvArr[1]);
        $this->assertEquals('1500.00', $csvArr[3]);
        $this->assertEquals('TIMOTHY', $csvArr[4]);
        $this->assertEquals('APPLEGATE', $csvArr[5]);
        $this->assertEquals('PMTCRED', $csvArr[6]);
        $this->assertEquals(123123, $csvArr[7]);
        $this->assertEquals('770 Broadway Manhattan New York NY 10003 #2-a BATCH# 125478', $csvArr[8]);
    }

    /**
     * @test
     */
    public function completeYardiXmlFormatForAllProperties()
    {
        $this->goToYardiReport();
        $dateStart = new DateTime('-45 days');
        $dateEnd = new DateTime();
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $begin->setValue($dateStart->format('m/d/Y'));
        $end->setValue($dateEnd->format('m/d/Y'));

        $this->page->pressButton('order.report.download');

        $xml = $this->page->getContent();
        $doc = new SimpleXMLElement($xml);

        $this->assertNotNull($receipts = $doc->Receipts);
        $this->assertNotNull($receipt = $receipts->Receipt);
        $this->assertNotNull($date = $receipt->Date);
        $this->assertNotNull($totalAmount = $receipt->TotalAmount);
        $this->assertNotNull($isCash = $receipt->IsCash);
        $this->assertNotNull($checkNumber = $receipt->CheckNumber);
        $this->assertNotNull($notes = $receipt->Notes);
        $this->assertNotNull($propertyId = $receipt->PropertyId);
        $this->assertNotNull($payerName = $receipt->PayerName);
        $this->assertNotNull($personId = $receipt->PersonId);
        $this->assertNotNull($postMonth = $receipt->PostMonth);

        $this->assertTrue(isset($receipt->BatchId));

        $this->assertEquals(100, (int) $receipt->PropertyId);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 456456', (string) $checkNumber);
        $this->assertEquals('t0013534', (string) $personId);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string) $notes);
    }
}
