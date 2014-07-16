<?php

namespace RentJeeves\CheckoutBundle\Tests\Payment;

use CreditJeeves\DataBundle\Entity\Group;
use Payum\Request\BinaryMaskStatusRequest;
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

        /** @var BinaryMaskStatusRequest $statusRequest */
        $statusRequest = $this->getContainer()
            ->get('payment.pay_credit_track')
            ->executePaymentAccount($group->getDepositAccount()->getPaymentAccounts()->first());

        $this->assertTrue($statusRequest->isSuccess(), $statusRequest->getModel()->getMessages());
    }
}
