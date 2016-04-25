<?php

namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;

/**
 * Service name "dod.payment_to_trusted_landlord_dtr"
 */
class PaymentToTrustedLandlordDTR implements DodRuleInterface
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $group = $payment->getContract()->getGroup();

        if ($group->getOrderAlgorithm() !== OrderAlgorithmType::PAYDIRECT) {
            return true;
        }

        if ($group->getTrustedLandlord() &&
            TrustedLandlordStatus::TRUSTED === $group->getTrustedLandlord()->getStatus()
        ) {
            return true;
        }
        $this->mailer->sendEmailPaymentFlaggedByUntrustedLandlordRule($payment->getContract());

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::DTR_UNTRUSTED_LANDLORD;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonMessage()
    {
        return 'Payment execution is not allowed for dtr group without trusted landlord';
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
