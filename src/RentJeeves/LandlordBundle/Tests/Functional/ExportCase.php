<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
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

        $this->assertNotNull($tenant);
        $this->assertNotNull($property);
        $this->assertNotNull($group);

        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setSum(999);
        $order->setUser($tenant);
        $oneWeekAgo = new DateTime();
        $oneWeekAgo->modify("-7 days");
        $order->setCreatedAt($oneWeekAgo);
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

        $transaction = new Transaction();
        $transaction->setIsSuccessful(false);
        $transaction->setOrder($order);
        $transaction->setTransactionId("1");
        $transaction->setAmount(999);
        $transaction->setMerchantName('MrchntNm');

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
        $this->page->clickLink('export');

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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->assertEquals(1500, $columns[3]);
        $this->assertEquals('770 Broadway Manhattan New York NY 10003 #2-a BATCH# 325698', $columns[8]);
    }

    public function exportByPromasCsv()
    {
        return [
            ['deposits', 7, 'uncheck'],
            ['payments', 9, 'uncheck'],
            ['deposits', 7, 'check'],
            ['payments', 9, 'check'],
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
        $this->page->clickLink('export');
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
        $this->assertNotNull($forAllGroubs = $this->page->find('css', '#base_order_report_type_includeAllGroups'));
        $forAllGroubs->$methodForAllGroups();
        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);

        $this->assertCount($countRows, $csvArr);

        $this->assertNotNull($csvArr = str_getcsv($csvArr[2]));
        $this->assertEquals('AAABBB-7', $csvArr[1]);
//        $this->assertEquals('1500.00', $csvArr[2]);
        $this->assertEquals('t0013534', $csvArr[4]);
    }

    /**
     * @test
     */
    public function promasBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('export');
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
        $this->assertEquals(4, $archive->numFiles);
        $file = $archive->getFromIndex(2);
        $rows = explode("\n", trim($file));
        $this->assertEquals(1, count($rows));
        $columns = explode(",", $rows[0]);
        $this->assertEquals('AAABBB-7', $columns[1]);
        $this->assertEquals(1500, $columns[2]);
        $this->assertEquals($columns[3], '"Trans #123123 Batch #125478"');
        $this->assertEquals("t0013534", $columns[4]);
    }

    /**
     * @test
     */
    public function yardiBatchReport()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('export');
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
        $this->assertEquals(10, $archive->numFiles);
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->assertEquals('1500', $csvArr[3]);
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
        $this->page->clickLink('export');
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
        $this->assertEquals('1500', $csvArr[3]);
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
        $this->page->clickLink('export');
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
