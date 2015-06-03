<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment;

use JMS\Serializer\Annotation as Serializer;

class Transaction
{
    /**
     * @Serializer\Type("ArrayCollection<RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment>")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\XmlList(inline = true, entry="PAYMENT")
     */
    protected $payments;

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }
}
