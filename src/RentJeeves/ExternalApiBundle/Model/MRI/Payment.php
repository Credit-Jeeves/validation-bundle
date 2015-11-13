<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

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
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\MRI\MRIOrder")
     * @Serializer\Groups({"MRI"})
     */
    protected $entryRequest;

    /**
     * @return MRIOrder
     */
    public function getEntryRequest()
    {
        return $this->entryRequest;
    }

    /**
     * @param MRIOrder $MRIOrder
     */
    public function setEntryRequest(MRIOrder $MRIOrder)
    {
        $this->entryRequest = $MRIOrder;
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
