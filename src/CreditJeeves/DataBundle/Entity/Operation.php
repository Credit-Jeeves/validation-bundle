<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Operation as Base;
use JMS\Serializer\Annotation as Serializer;

/**
 * Operation
 *
 * @ORM\Table(name="cj_operation")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OperationRepository")
 */
class Operation extends Base
{
    /**
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Notes")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return DateTime
     */
    public function getNotes()
    {
        return $this->getCreatedAt();
    }

    /**
     * Add orders
     *
     * @param \CreditJeeves\DataBundle\Entity\Order $orders
     * @return Operation
     */
    public function setOrders($orders)
    {
        if (is_object($orders)) {
            $this->addOrder($orders);
            $orders->addOperation($this);
        }

        foreach ($orders as $order) {
            $this->addOrder($order);
            $order->addOperation($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getType();
    }
}
