<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionCharge
{
    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionChargeDetail")
     */
    protected $detail;

    /**
     * @param ResidentTransactionChargeDetail $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return ResidentTransactionChargeDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
