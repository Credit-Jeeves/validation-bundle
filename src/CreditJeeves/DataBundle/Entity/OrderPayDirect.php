<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\OutboundTransaction;

/**
 * @ORM\Entity
 */
class OrderPayDirect extends BaseOrder
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
     * @return BaseOrder
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
}
