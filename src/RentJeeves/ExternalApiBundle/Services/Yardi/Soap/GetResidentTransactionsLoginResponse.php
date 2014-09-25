<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionProperty;

/**
 * @Serializer\XmlRoot("ResidentTransactions")
 */
class GetResidentTransactionsLoginResponse
{
    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionProperty")
     */
    protected $property;

    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return ResidentTransactionProperty
     */
    public function getProperty()
    {
        return $this->property;
    }
}
