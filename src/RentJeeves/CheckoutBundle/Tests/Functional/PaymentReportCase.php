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

        $count = $paymentReport->synchronize();
        $this->assertEquals(6, $count);

        $this->assertEquals($secondStatus, $order->getStatus());
    }

    public function provide()
    {
        return array(
            array('369369', 'complete', 'returned'),
            array('258258', 'new', 'complete'),
            array('123123', 'complete', 'refunded'),
            array('456456', 'complete', 'cancelled'),
        );
    }
} 
