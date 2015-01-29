<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class RtCustomer
{
    /**
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customerId;

    /**
     * @Serializer\SerializedName("Customers")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Customers")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customers;

    /**
     * @Serializer\SerializedName("RT_Unit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtUnit;

    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtServiceTransactions;

    /**
     * @Serializer\SerializedName("PaymentAccepted")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $paymentAccepted;

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
     * @return RtServiceTransactions
     */
    public function getRtServiceTransactions()
    {
        return $this->rtServiceTransactions;
    }

    /**
     * @param RtServiceTransactions $rtServiceTransactions
     */
    public function setRtServiceTransactions(RtServiceTransactions $rtServiceTransactions)
    {
        $this->rtServiceTransactions = $rtServiceTransactions;
    }

    /**
     * @return RtUnit
     */
    public function getRtUnit()
    {
        return $this->rtUnit;
    }

    /**
     * @param RtUnit $rtUnit
     */
    public function setRtUnit(RtUnit $rtUnit)
    {
        $this->rtUnit = $rtUnit;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return Customers
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
}
