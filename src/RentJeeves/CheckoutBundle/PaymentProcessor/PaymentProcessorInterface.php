<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface PaymentProcessorInterface
{
    public function createPaymentAccount(PaymentAccountData $data, User $user, Group $group);

    public function executeOrder(
        Order $order,
        PaymentAccount $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    );

    public function loadReport($reportType);
}
