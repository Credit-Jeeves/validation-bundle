<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("DETAIL")
 */
class Payment
{
    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\PaymentDetail")
     * @Serializer\SerializedName("DETAIL")
     */
    protected $detail;

    /**
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }
}
