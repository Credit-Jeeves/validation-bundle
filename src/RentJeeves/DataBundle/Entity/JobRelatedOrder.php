<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\Mapping as ORM;

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
