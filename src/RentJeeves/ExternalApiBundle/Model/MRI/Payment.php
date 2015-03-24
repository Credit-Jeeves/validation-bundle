<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;

/** @Serializer\XmlRoot("mri_s-pmrm_paymentdetailsbypropertyid") */
class Payment
{
    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\MRI\Entry")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $entryResponse;

    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"MRI"})
     */
    protected $entryRequest;

    /**
     * @return Order
     */
    public function getEntryRequest()
    {
        return $this->entryRequest;
    }

    /**
     * @param Order $entry
     */
    public function setEntryRequest(Order $entry)
    {
        $this->entryRequest = $entry;
    }

    /**
     * @return Entry
     */
    public function getEntryResponse()
    {
        return $this->entryResponse;
    }

    /**
     * @param Entry $entryResponse
     */
    public function setEntryResponse(Entry $entryResponse)
    {
        $this->entryResponse = $entryResponse;
    }
}
