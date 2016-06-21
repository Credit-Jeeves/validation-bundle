<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;

class PayCreditTrackCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executePaymentAccount()
    {
        $this->load(true);

        /** @var Tenant $user */
        $user = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $paymentAccount = $user->getPaymentAccounts()->first();

        /** @var OrderSubmerchant $order */
        $order = $this->getContainer()
            ->get('payment.pay_credit_track')
            ->executePaymentAccount($paymentAccount);

        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
    }
}
