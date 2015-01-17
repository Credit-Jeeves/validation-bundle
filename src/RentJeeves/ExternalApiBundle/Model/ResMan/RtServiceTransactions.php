<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class RtServiceTransactions
{
    /**
     * @Serializer\SerializedName("Transactions")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Transactions")
     * @Serializer\Groups({"ResMan"})
     */
    protected $transactions;

    /**
     * @return Transactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Transactions $transactions
     */
    public function setTransactions(Transactions $transactions)
    {
        $this->transactions = $transactions;
    }
}
