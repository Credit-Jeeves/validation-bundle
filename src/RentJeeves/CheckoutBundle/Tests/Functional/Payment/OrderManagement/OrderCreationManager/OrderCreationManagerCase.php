<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional\Payment\OrderManagement\OrderCreationManager;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\TypeDebitFee;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class OrderCreationManagerCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @return array
     */
    public function getDataForDebitFee()
    {
        return [
            [TypeDebitFee::FLAT_FEE, 1],
            [TypeDebitFee::PERCENTAGE, 10]
        ];
    }

    /**
     * @test
     * @dataProvider getDataForDebitFee
     */
    public function shouldSetCorrectFeeForDebitCard($typeDebitFee, $expectedFee)
    {
        $payment = new Payment();
        $contract = new Contract();
        $group = new Group();
        $groupSettings = $group->getGroupSettings();
        $groupSettings->setAllowedDebitFee(true);
        $groupSettings->setTypeDebitFee($typeDebitFee);
        $groupSettings->setDebitFee(1);

        $user = new Tenant();
        $paymentAccount = new PaymentAccount();
        $paymentAccount->setUser($user);
        $paymentAccount->setType(PaymentAccountType::DEBIT_CARD);
        $paymentAccount->setRegistered(true);
        $depositAccount = new DepositAccount();
        $payment->setDepositAccount($depositAccount);

        $contract->setGroup($group);
        $payment->setContract($contract);
        $payment->setPaymentAccount($paymentAccount);
        $payment->setAmount(1000);

        $order = $this->getOrderCreationManager()->createRentOrder($payment);

        $this->assertEquals($expectedFee, $order->getFee(), 'Expected fee not equals actual fee');
    }

    /**
     * @return OrderCreationManager
     */
    protected function getOrderCreationManager()
    {
        return new OrderCreationManager(
            $this->getEntityManagerMock(),
            $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', [], [], '', false),
            'test',
            'test'
        );
    }
}
