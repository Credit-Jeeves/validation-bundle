<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionPropertyCustomer
{
    /**
     * @Serializer\SerializedName("Customers")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Customers")
     */
    protected $customers;

    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionServiceTransactions")
     */
    protected $serviceTransactions;

    /**
     * It's leas id of Contract
     *
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
     * @Serializer\SerializedName("PaymentAccepted")
     * @Serializer\Type("string")
     */
    protected $paymentAccepted;

    /**
     * @return array
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @param Customers $customers
     */
    public function setCustomers(Customers $customers)
    {
        $this->customers = $customers;
    }

    /**
     * @return string
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param string $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

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

    public function getLeaseId()
    {
        return $this->getCustomerId();
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
