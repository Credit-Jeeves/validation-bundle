<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Operation as Base;

/**
 * Operation
 *
 * @ORM\Table(name="cj_operation")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OperationRepository")
 */
class Operation extends Base
{

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
        }

        foreach ($orders as $order) {
            $this->addOrder($order);
        }

        return $this;
    }
}
