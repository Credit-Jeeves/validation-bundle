<?php

namespace RentJeeves\ExternalApiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class RtCustomer
{
    /**
     * @Serializer\SerializedName("RtServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\RtServiceTransactions")
     * @Serializer\Groups({"soapYardiRequest"})
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
