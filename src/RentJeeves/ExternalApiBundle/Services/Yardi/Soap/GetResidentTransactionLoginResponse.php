<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ResidentTransactions")
 */
class GetResidentTransactionLoginResponse
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
        $this->property[] = $property;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }
}
