<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class RtServiceTransactions
{
    /**
     * @Serializer\SerializedName("Transactions")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\ResMan\Transactions>")
     * @Serializer\XmlList(inline = true, entry="Transactions")
     * @Serializer\Groups({"ResMan"})
     */
    protected $transactions = [];

    /**
     * @return Transactions[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Transactions $transactions
     */
    public function addTransactions(Transactions $transactions)
    {
        $this->transactions[] = $transactions;
    }
}
