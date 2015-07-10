<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;

/**
 * @ORM\Entity
 */
class OrderPayDirect extends Order
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\OutboundTransaction",
     *     mappedBy="order",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @var ArrayCollection
     */
    protected $outboundTransactions;

    public function __construct()
    {
        parent::__construct();
        $this->outboundTransactions = new ArrayCollection();
    }

    /**
     * Add OutboundTransaction
     *
     * @param OutboundTransaction $transaction
     */
    public function addOutboundTransaction(OutboundTransaction $transaction)
    {
        $this->outboundTransactions[] = $transaction;
    }

    /**
     * Remove OutboundTransaction
     *
     * @param OutboundTransaction $transaction
     */
    public function removeOutboundTransaction(OutboundTransaction $transaction)
    {
        $this->outboundTransactions->removeElement($transaction);
    }

    /**
     * Get OutboundTransaction
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOutboundTransactions()
    {
        return $this->outboundTransactions;
    }

    /**
     * @return OutboundTransaction|boolean
     */
    public function getDepositOutboundTransaction()
    {
        return $this->getOutboundTransactions()->filter(
            function (OutboundTransaction $transaction) {
                return OutboundTransactionType::DEPOSIT === $transaction->getType();
            }
        )->first();
    }

    /**
     * @return OutboundTransaction|boolean
     */
    public function getReversalOutboundTransaction()
    {
        return $this->getOutboundTransactions()->filter(
            function (OutboundTransaction $transaction) {
                return OutboundTransactionType::REVERSAL === $transaction->getType();
            }
        )->first();
    }
}
