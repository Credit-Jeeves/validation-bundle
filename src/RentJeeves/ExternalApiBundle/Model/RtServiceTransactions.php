<?php

namespace RentJeeves\ExternalApiBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class RtServiceTransactions
{
    /**
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\Transactions>")
     * @Serializer\Groups({"soapYardiRequest"})
     * @Serializer\XmlList(inline = true, entry="Transactions")
     * @Serializer\XmlKeyValuePairs
     */
    protected $transactions = array();

    public function __construct($orders = null)
    {
        if (!$orders) {
            return;
        }

        foreach ($orders as $order) {
            $this->transactions[] = new Transactions($order);
        }
    }

    /**
     * @param Transactions $transactions
     */
    public function setTransactions(Transactions $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @return Transactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}
