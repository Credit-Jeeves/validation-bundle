<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionPropertyCustomer
{
    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionServiceTransactions")
     */
    protected $serviceTransactions;

    /**
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     */
    protected $customerId;

    /**
     * @Serializer\SerializedName("RT_Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionUnit")
     */
    protected $unit;

    /**
     * @param mixed $serviceTransactions
     */
    public function setServiceTransactions($serviceTransactions)
    {
        $this->serviceTransactions = $serviceTransactions;
    }

    /**
     * @return mixed
     */
    public function getServiceTransactions()
    {
        return $this->serviceTransactions;
    }

    /**
     * @param mixed $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
