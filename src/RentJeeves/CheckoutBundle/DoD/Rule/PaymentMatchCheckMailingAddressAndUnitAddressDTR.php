<?php
namespace RentJeeves\CheckoutBundle\DoD\Rule;

use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentFlaggedReason;

/**
 * Service name "dod.payment_match_check_mailing_address_and_unit_address_dtr"
 */
class PaymentMatchCheckMailingAddressAndUnitAddressDTR implements DodPaymentRuleInterface
{
    /** @var Payment $payment */
    protected $payment;

    /**
     * {@inheritdoc}
     */
    public function checkPayment(Payment $payment)
    {
        $this->payment = $payment;
        $contract = $payment->getContract();

        $group = $contract->getGroup();
        if ($group->getOrderAlgorithm() !== OrderAlgorithmType::PAYDIRECT) {
            return true;
        }

        $trustedLandlord = $group->getTrustedLandlord();
        if ($trustedLandlord) {
            $checkMailingAddress = $trustedLandlord->getCheckMailingAddress();
            $propertyAddress = $contract->getProperty()->getPropertyAddress();

            return $checkMailingAddress->getIndex() !== $propertyAddress->getIndex();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonCode()
    {
        return PaymentFlaggedReason::DTR_PAYMENT_MATCH_ADDRESSES;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonMessage()
    {
        return sprintf(
            'Payment#%s has the same check mailing address as tenant\'s unit address',
            $this->payment->getId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function support($object)
    {
        return $object instanceof Payment;
    }
}
