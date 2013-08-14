<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 * @ORM\Table(name="cj_order")
 * @ORM\HasLifecycleCallbacks()
 */
class Order extends BaseOrder
{
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updated_at = new \DateTime();
    }

    public function setOperations($operation)
    {
        if (is_object($orders)) {
            $this->addOperation($operation);
        }

        foreach ($orders as $order) {
            $this->addOperation($operation);
        }

        return $this;
    }

    public function getItem()
    {
        $result = array();
        $contract = $this->getOperations()->last()->getContract();
        $result['amount'] = $this->getAmount();
        $result['tenant'] = $contract->getTenant()->getFullName();
        $result['address'] = $contract->getRentAddress($contract->getProperty(), $contract->getUnit());
        $result['start'] = $this->getHeartlands()->first()->getCreatedAt()->format('m/d/Y');
        $result['status'] = 'PENDING';
        $result['finish'] = '--';
        $result['style'] = 'contract-pending';
        if (OrderStatus::COMPLETE == $this->getStatus()) {
            $result['finish'] = $this->getHeartlands()->last()->getCreatedAt()->format('m/d/Y');
            $result['style'] = '';
            $result['status'] = 'PAID';
        }
        return $result;
    }
}
