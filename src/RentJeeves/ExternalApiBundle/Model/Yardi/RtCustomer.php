<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

class RtCustomer
{
    /**
     * @Serializer\SerializedName("RTServiceTransactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Yardi\RtServiceTransactions")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     */
    protected $rtServiceTransactions;

    public function __construct(YardiSettings $yardiSettings, $orders = null)
    {
        $this->rtServiceTransactions = new RtServiceTransactions($yardiSettings, $orders);
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
