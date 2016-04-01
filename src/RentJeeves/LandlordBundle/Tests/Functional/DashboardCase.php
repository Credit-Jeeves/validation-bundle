<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class DashboardCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSortOrderByStatus()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->loginByAccessToken('landlord1@example.com');

        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->assertNotNull($this->page->findAll('css', '#payments-block td'));

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");
        $this->assertNotNull($span = $this->page->findAll('css', '#payments-block-tbody td>span'));
        $this->assertEquals(
            'order.status.text.cancelled',
            $span[0]->getText(),
            sprintf('Wrong text in field: expected order.status.text.cancelled, got %s', $span[0]->getText())
        );

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");
        $this->assertNotNull($span = $this->page->findAll('css', '#payments-block-tbody td>span'));
        $this->assertEquals('order.status.text.returned', $span[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($propertyA = $this->page->find('css', '#propertyA'));
        $propertyA->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#actions-block td'));
    }

    /**
     * @test
     */
    public function search()
    {
        $this->setDefaultSession('selenium2');
        $this->load();
        $this->loginByAccessToken('landlord1@example.com');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (39)', $allh2->getText(), 'Wrong count');

        $this->assertNotNull($searchPayments_link = $this->page->find('css', '#searchPayments_link'));
        $searchPayments_link->click();
        $this->assertNotNull($tenant = $this->page->find('css', '#searchPayments_li_2'));
        $tenant->click();
        $this->assertNotNull($searchField = $this->page->find('css', '#searsh-field-payments'));
        $searchField->setValue('John2');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments'));
        $searchSubmit->click();
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', 'h3.processPayment'));
        $this->assertEquals('donthavedata', $allh2->getText(), 'Wrong count');

        $this->assertNotNull($delete = $this->page->find('css', '#payments-block .pie-el'));
        $delete->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (39)', $allh2->getHtml(), 'Wrong count');
    }

    /**
     * @test
     */
    public function returnedRefundedFilter()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->loginByAccessToken('landlord1@example.com');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($searchPaymentsStatus = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchPaymentsStatus->click();

        $this->assertNotNull(
            $returned = $this->page->find('css', '#searchPaymentsStatus_list li[data-value="returned"]')
        );
        $this->assertNotNull(
            $refunded = $this->page->find('css', '#searchPaymentsStatus_list li[data-value="refunded"]')
        );
        /*
         * Check returned Status
         */
        $returned->click();

        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");
        /*
         * Get first TD with status
         */
        $this->assertNotNull($td = $this->page->find('css', '#payments-block-tbody .actions-status span'));
        $this->assertEquals('order.status.text.returned', $td->getHtml());
    }

    /**
     * @test
     */
    public function showCashPayment()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $order = $em->getRepository('DataBundle:Order')->findOneBy(['sum' => 3700]);
        $order->setPaymentType(OrderPaymentType::CASH);
        $em->flush($order);
        $this->loginByAccessToken('landlord1@example.com');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");
        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'));

        $this->assertEquals('payments.total (38)', $title->getHtml());
        $this->assertNotNull($searchPaymentsLink = $this->page->find('css', '.externalPaymentsBlock>input'));
        $searchPaymentsLink->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (39)', $title->getHtml());
    }

    /**
     * @test
     */
    public function searchByCheckNumber()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->loginByAccessToken('landlord1@example.com');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull(
            $allh2 = $this->page->find('css', '#payments-block .title-box>h2'),
            'Should be title Payments Total'
        );
        $this->assertEquals('payments.total (39)', $allh2->getText(), 'Wrong count');

        $this->assertNotNull(
            $searchPayments_link = $this->page->find('css', '#searchPayments_link'),
            'On the page should be button for search'
        );
        $searchPayments_link->click();

        $this->assertNotNull($checkNumberFilter = $this->page->find('css', '#searchPayments_li_4'),
            'Should be options Check Number');
        $checkNumberFilter->click();
        $searchField = $this->page->find('css', '#searsh-field-payments');
        $this->assertNotNull($searchField, 'Should be input for search value');

        //test when wrong check number value
        $searchField->setValue('test');
        $this->assertNotNull(
            $searchSubmit = $this->page->find('css', '#search-submit-payments', 'Should be input for submit button')
        );
        $searchSubmit->click();
        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', 'h3.processPayment'));
        $this->assertEquals('donthavedata', $allh2->getText(), 'Wrong count');

        //test with check number value is exist in DB
        $searchField->setValue('123456');
        $searchSubmit->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (1)', $allh2->getText(), 'Wrong count: Should be 1 result');

        $this->assertNotNull($delete = $this->page->find('css', '#payments-block .pie-el'));
        $delete->click();

        $this->session->wait(5000, "!$('img.processPayment').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (39)', $allh2->getHtml(), 'Wrong count');
    }
}
