<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan\Transaction;

use JMS\Serializer\Annotation as Serializer;

class RtCustomer
{
    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Transaction\RtServiceTransactions")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtServiceTransactions;

    public function __construct($orders = null)
    {
        $this->rtServiceTransactions = new RtServiceTransactions($orders);
    }

    /**
     * @param RtServiceTransactions $rtServiceTransactions
     */
    public function setRtServiceTransactions(RtServiceTransactions $rtServiceTransactions)
    {
        $this->rtServiceTransactions = $rtServiceTransactions;
    }

    /**
     * @return RtServiceTransactions
     */
    public function getRtServiceTransactions()
    {
        return $this->rtServiceTransactions;
    }
}
