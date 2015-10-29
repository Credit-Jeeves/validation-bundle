<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

class RtServiceTransactions
{
    /**
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\Yardi\Transactions>")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     * @Serializer\XmlList(inline = true, entry="Transactions")
     * @Serializer\XmlKeyValuePairs
     */
    protected $transactions = array();

    public function __construct(YardiSettings $yardiSettings, $orders = null)
    {
        if (!$orders) {
            return;
        }

        foreach ($orders as $order) {
            $this->transactions[] = new Transactions($yardiSettings, $order);
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
