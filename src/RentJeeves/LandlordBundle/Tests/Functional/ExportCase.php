<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;
use \SimpleXMLElement;
use ZipArchive;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ExportCase extends BaseTestCase
{
    /**
     * @test
     */
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
        $this->assertEquals(6, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($property = $this->page->find('css', '#base_order_report_type_property'));

        $this->assertNotNull($propertyId = $this->page->find('css', '#base_order_report_type_propertyId'));
        $this->assertNotNull($accountId = $this->page->find('css', '#base_order_report_type_accountId'));
        $this->assertNotNull($arAccountId = $this->page->find('css', '#base_order_report_type_arAccountId'));

        $propertyId->setValue(100);
        $accountId->setValue(88);
        $arAccountId->setValue(77);
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $property->selectOption(1);
    }

    /**
     * @depends goToYardiReport
     * @test
     */
    public function baseXmlFormat()
    {
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
        $this->assertNotNull($details = $receipt->Details->Detail);
        $this->assertNotNull($amount = $details->Amount);
        $this->assertNotNull($notesDetail = $details->Notes);

        $this->assertEquals(100, (int) $receipt->PropertyId);
        $this->assertEquals(88, (int) $details->AccountId);
        $this->assertEquals(77, (int) $details->ArAccountId);
        $this->assertEquals(100, (int) $details->PropertyId);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 123123', (string) $checkNumber);
        $this->assertEquals('FGDTRFG-44', (string) $personId);
        $this->assertEquals('1500.00', (string)$amount);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string)$notes);

    }

    /**
     * @test
     */
    public function reversalYardiXmlFormat()
    {
        $this->goToYardiReport();

        $date = new DateTime('-27 days');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $begin->setValue($date->format('m/d/Y'));
        $end->setValue($date->format('m/d/Y'));

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
        $this->assertNotNull($details = $receipt->Details->Detail);
        $this->assertNotNull($amount = $details->Amount);
        $this->assertNotNull($notesDetail = $details->Notes);

        $this->assertNotNull($batchId = $receipt->BatchId);
        $this->assertNotNull($originalReceiptDate = $receipt->OriginalReceiptDate);
        $this->assertNotNull($returnType = $receipt->ReturnType);

        $this->assertEquals(100, (int) $receipt->PropertyId);
        $this->assertEquals(88, (int) $details->AccountId);
        $this->assertEquals(77, (int) $details->ArAccountId);
        $this->assertEquals(100, (int) $details->PropertyId);

        $this->assertEquals('700.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 55123260', (string) $checkNumber);
        $this->assertEquals('FGDTRFG-44', (string) $personId);
        $this->assertEquals('700.00', (string) $amount);
        $this->assertEquals('Reverse for Trans ID 55123260', (string) $notes);
        $this->assertEquals('Reverse', (string) $returnType);
        $this->assertEquals('0', (string) $batchId);
    }

    /**
     * @test
     */
    public function completeYardiXmlFormat()
    {
        $this->goToYardiReport();

        $date = new DateTime('-9 days');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $begin->setValue($date->format('m/d/Y'));
        $end->setValue($date->format('m/d/Y'));

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
        $this->assertNotNull($details = $receipt->Details->Detail);
        $this->assertNotNull($amount = $details->Amount);
        $this->assertNotNull($notesDetail = $details->Notes);

        $this->assertTrue(!isset($receipt->BatchId));
        $this->assertTrue(!isset($receipt->OriginalReceiptDate));
        $this->assertTrue(!isset($receipt->ReturnType));

        $this->assertEquals(100, (int) $receipt->PropertyId);
        $this->assertEquals(88, (int) $details->AccountId);
        $this->assertEquals(77, (int) $details->ArAccountId);
        $this->assertEquals(100, (int) $details->PropertyId);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('false', (string) $isCash);
        $this->assertEquals('PMTCRED 147147', (string) $checkNumber);
        $this->assertEquals('FGDTRFG-44', (string) $personId);
        $this->assertEquals('1500.00', (string)$amount);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string)$notes);
    }

    /**
     * @test
     */
    public function baseCsvFormat()
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
        $this->assertEquals(6, count($errors));

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('csv');
        $this->page->pressButton('order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(3, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($property = $this->page->find('css', '#base_order_report_type_property'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $property->selectOption(1);

        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);
        $this->assertTrue(isset($csvArr[0]));
        $header = 'Property,Unit,Date,TotalAmount,First_Name,Last_Name,Code,Description';
        $this->assertEquals($header, $csvArr[0]);

        $this->assertNotNull($csvArr = str_getcsv($csvArr[1]));
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003', $csvArr[0]);
        $this->assertEquals('2-a', $csvArr[1]);
        $this->assertNotNull($csvArr[2]);
        $this->assertEquals('1500.00', $csvArr[3]);
        $this->assertEquals('TIMOTHY', $csvArr[4]);
        $this->assertEquals('APPLEGATE', $csvArr[5]);
        $this->assertEquals('PMTCRED', $csvArr[6]);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a PMTCRED 123123', $csvArr[7]);
    }

    /**
     * @test
     */
    public function promasCsvFormat()
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
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));

        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);
        $this->assertEquals(5, count($csvArr));
        $this->assertNotNull($csvArr = str_getcsv($csvArr[2]));
        $this->assertEquals('AAABBB-7', $csvArr[1]);
        $this->assertEquals('1500.00', $csvArr[2]);
        $this->assertEquals('FGDTRFG-44', $csvArr[4]);
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
        $this->assertEquals(3, $archive->numFiles);
        $file = $archive->getFromIndex(1);
        $rows = explode("\n", trim($file));
        $this->assertEquals(1, count($rows));
        $columns = explode(",", $rows[0]);
        $this->assertEquals('AAABBB-7', $columns[1]);
        $this->assertEquals(1500, $columns[2]);
        $this->assertEquals($columns[3], '"Trans #123123 Batch #125478"');
        $this->assertEquals("FGDTRFG-44", $columns[4]);
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
        $this->assertNotNull($property = $this->page->find('css', '#base_order_report_type_property'));

        $this->assertNotNull($propertyId = $this->page->find('css', '#base_order_report_type_propertyId'));
        $this->assertNotNull($accountId = $this->page->find('css', '#base_order_report_type_accountId'));
        $this->assertNotNull($arAccountId = $this->page->find('css', '#base_order_report_type_arAccountId'));
        $this->assertNotNull($makeZip = $this->page->find('css', '#base_order_report_type_makeZip'));

        $propertyId->setValue(100);
        $accountId->setValue(88);
        $arAccountId->setValue(77);
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $property->selectOption(1);
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
        $this->assertNotNull($details = $receipt->Details->Detail);
        $this->assertNotNull($notes = $receipt->Notes);
        $this->assertNotNull($amount = $details->Amount);
        $this->assertNotNull($notesDetail = $details->Notes);

        $this->assertEquals('1500.00', (string) $totalAmount);
        $this->assertEquals('FGDTRFG-44', (string) $personId);
        $this->assertEquals('1500.00', (string)$amount);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string)$notes);
    }

    /**
     * @test
     */
    public function rentTrackCsvFormat()
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

        $this->page->pressButton('order.report.download');
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));

        $this->assertEquals($notice->getText(), 'export.no_data');

        $beginD->modify('-1 year');
        $begin->setValue($beginD->format('m/d/Y'));

        $this->page->pressButton('order.report.download');

        $csv = $this->page->getContent();

        $csvFullArr = explode("\n", $csv);
        $this->assertEquals(17, count($csvFullArr));
        /** check Last */
        $this->assertNotNull($csvArr = str_getcsv($csvFullArr[9]));
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003', $csvArr[1]);
        $this->assertEquals('AAABBB-7', $csvArr[2]);
        $this->assertEquals('456456', $csvArr[7]);
        $this->assertEquals('325698', $csvArr[8]);
        $this->assertEquals('FGDTRFG-44', $csvArr[4]);
        $this->assertEquals('15235678', $csvArr[13]);
        /** check Refunded */
        $this->assertNotNull($csvArr = str_getcsv($csvFullArr[13]));
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

        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));
        $makeZip->check();

        $this->page->pressButton('order.report.download');
        $csvZip = $this->session->getDriver()->getContent();

        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export.zip';
        file_put_contents($testFile, $csvZip);

        $archive = new ZipArchive();
        $this->assertTrue($archive->open($testFile, ZipArchive::CHECKCONS));
        $this->assertEquals(9, $archive->numFiles);
        $file = $archive->getFromIndex(2);
        $rows = explode("\n", trim($file));
        $this->assertEquals(3, count($rows));
        $columns = str_getcsv($rows[1]);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003', $columns[1]);
        $this->assertEquals('15235678', $columns[13]);
    }
}
