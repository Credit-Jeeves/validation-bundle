<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;
use \SimpleXMLElement;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ReportsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function baseXmlFormat()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.reports');

        $beginD = new DateTime();
        $beginD->modify('-1 month');
        $endD = new DateTime();

        $this->page->pressButton('base.order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(5, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $this->assertNotNull($propertyId = $this->page->find('css', '#base_order_report_type_propertyId'));
        $this->assertNotNull($accountId = $this->page->find('css', '#base_order_report_type_accountId'));
        $this->assertNotNull($arAccountId = $this->page->find('css', '#base_order_report_type_arAccountId'));

        $propertyId->setValue(100);
        $accountId->setValue(88);
        $arAccountId->setValue(77);
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));

        $this->page->pressButton('base.order.report.download');

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
        $this->assertEquals('PMTCRED 123456', (string) $checkNumber);
        $this->assertEquals('TIMOTHY APPLEGATE', (string) $payerName);
        $this->assertEquals('37200.00', (string)$amount);
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a', (string)$notes);

    }

    /**
     * @test
     */
    public function baseCsvFormat()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.reports');

        $beginD = new DateTime();
        $beginD->modify('-1 month');
        $endD = new DateTime();

        $this->page->pressButton('base.order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(5, count($errors));

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('csv');
        $this->page->pressButton('base.order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(2, count($errors));
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));

        $this->page->pressButton('base.order.report.download');

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
        $this->assertEquals('770 Broadway, Manhattan, New York, NY 10003 #2-a PMTCRED 123456', $csvArr[7]);
    }
}
