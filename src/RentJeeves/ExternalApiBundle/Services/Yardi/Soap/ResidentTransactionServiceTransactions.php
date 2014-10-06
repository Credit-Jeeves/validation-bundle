<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionServiceTransactions 
{
    /**
     * @Serializer\SerializedName("Transactions")
     * @Serializer\XmlList(inline = true, entry="Transactions")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionTransactions>")
     */
    protected $transactions = [];

    /**
     * @param mixed $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions[] = $transactions;
    }

    /**
     * @return mixed
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

} 
