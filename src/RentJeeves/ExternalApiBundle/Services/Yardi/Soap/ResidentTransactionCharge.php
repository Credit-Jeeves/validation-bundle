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
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
