<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;
use \DOMDocument;

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
        $beginD->modify('-22 month');
        $endD = new DateTime();

        $this->page->pressButton('base.order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(5, count($errors));

        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_propertyId'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));

        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));

        $this->page->pressButton('base.order.report.download');

        $xml = $this->page->getContent();
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $this->assertNotNull($receipts = $doc->getElementsByTagName('Receipts')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('Receipt')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('Date')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('TotalAmount')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('IsCash')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('CheckNumber')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('Notes')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('PayerName')->item(0));
        $this->assertNotNull($receipt = $doc->getElementsByTagName('PostMonth')->item(0));
        $this->assertNotNull($details = $doc->getElementsByTagName('Details')->item(0));
        $this->assertNotNull($details = $doc->getElementsByTagName('Amount')->item(0));
        $this->assertNotNull($details = $doc->getElementsByTagName('Notes')->item(0));
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
        $beginD->modify('-22 month');
        $endD = new DateTime();

        $this->page->pressButton('base.order.report.download');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list>li'));
        $this->assertEquals(5, count($errors));

        $this->assertNotNull($type = $this->page->find('css', '#base_order_report_type_type'));
        $type->selectOption('csv');
        $this->assertNotNull($begin = $this->page->find('css', '#base_order_report_type_begin'));
        $this->assertNotNull($end = $this->page->find('css', '#base_order_report_type_end'));
        $begin->setValue($beginD->format('m/d/Y'));
        $end->setValue($endD->format('m/d/Y'));

        $this->page->pressButton('base.order.report.download');

        $csv = $this->page->getContent();
        $csvArr = explode("\n", $csv);
        $this->assertTrue(isset($csvArr[0]));
        $header = explode(',', $csvArr[0]);
        $this->assertTrue(in_array('Property', $header));
        $this->assertTrue(in_array('Unit', $header));
        $this->assertTrue(in_array('Date', $header));
        $this->assertTrue(in_array('TotalAmount', $header));
        $this->assertTrue(in_array('First_Name', $header));
        $this->assertTrue(in_array('Last_Name', $header));
        $this->assertTrue(in_array('Code', $header));
        $this->assertTrue(in_array('Description', $header));

        $this->assertNotNull(str_getcsv($csv, ',', '"'));
    }
}
