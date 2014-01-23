<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class JobRelatedOrder extends JobRelatedEntities
{
    /**
     * @ORM\ManyToOne(targetEntity = "\CreditJeeves\DataBundle\Entity\Order", inversedBy = "jobs")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    protected $order;
}
