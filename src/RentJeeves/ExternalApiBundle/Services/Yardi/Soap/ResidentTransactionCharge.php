<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionCharge
{
    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionDetail")
     */
    protected $detail;

    /**
     * @param ResidentTransactionDetail $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return ResidentTransactionDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
