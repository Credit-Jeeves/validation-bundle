<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Order;

/**
 * @ORM\Entity
 */
class JobRelatedOrder extends JobRelatedEntities
{
    /**
     * @ORM\ManyToOne(
     *      targetEntity = "\CreditJeeves\DataBundle\Entity\Order",
     *      inversedBy = "jobs",
     *      fetch = "EAGER",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    protected $order;

    /**
     * @param Order $order
     *
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
