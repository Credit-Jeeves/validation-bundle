<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provide
     */
    public function shouldSynchronizeDBOrdersWithReport($transactionId, $firstStatus, $secondStatus)
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $paymentReport = $this->getContainer()->get('payment.report');

        $repo = $em->getRepository('RjDataBundle:Heartland');
        $transaction = $repo->findOneBy(array('transactionId' => $transactionId));
        $order = $transaction->getOrder();

        $this->assertEquals($firstStatus, $order->getStatus());

        $this->clearEmail();
        $count = $paymentReport->synchronize();
        $this->assertEquals(7, $count);

        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($emails = $this->page->findAll('css', 'a'));
        $this->assertCount(4, $emails);

        $this->assertEquals($secondStatus, $order->getStatus());
    }

    public function provide()
    {
        return array(
            array('369369', 'complete', 'returned'),
            array('258258', 'new', 'cancelled'),
            array('123123', 'complete', 'refunded'),
            array('456456', 'complete', 'cancelled'),
        );
    }
}
