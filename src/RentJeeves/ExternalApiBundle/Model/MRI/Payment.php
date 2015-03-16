<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;

/** @Serializer\XmlRoot("mri_s-pmrm_paymentdetailsbypropertyid") */
class Payment
{
    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"MRI"})
     */
    protected $entry;

    /**
     * @return Order
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param Order $entry
     */
    public function setEntry(Order $entry)
    {
        $this->entry = $entry;
    }
}
