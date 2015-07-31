<?php

namespace RentJeeves\CheckoutBundle\Tests\Payment;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\TestBundle\BaseTestCase;

class PayCreditTrackCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executePaymentAccount()
    {
        $this->load(true);

        /** @var Group $group */
        $group = $this->getContainer()
            ->get('doctrine')
            ->getRepository('DataBundle:Group')
            ->findOneByCode($this->getContainer()->getParameter('rt_merchant_name'));

        /** @var OrderSubmerchant $order */
        $order = $this->getContainer()
            ->get('payment.pay_credit_track')
            ->executePaymentAccount(
                $group->getRentDepositAccountForCurrentPaymentProcessor()->getPaymentAccounts()->first()
            );

        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
    }
}
