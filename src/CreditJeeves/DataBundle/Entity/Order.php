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
     * @var string
     */
    const STATUS_NEWONE = 'PENDING';

    /**
     * @var string
     */
    const STATUS_COMPLETE = 'PAID';

    /**
     * @var string
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * @var string
     */
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @var string
     */
    const STATUS_REFUNDED = 'REFUNDED';

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
        $result['start'] = $this->getCreatedAt()->format('m/d/Y');
        $result['status'] = self::STATUS_NEWONE;
        $result['finish'] = '--';
        $result['style'] = 'contract-pending';
        $result['icon'] = $this->getOrderTypes();
        $status = $this->getStatus();
        switch ($status) {
            case OrderStatus::NEWONE:
                break;
            case OrderStatus::COMPLETE:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = '';
                $result['status'] = self::STATUS_COMPLETE;
                break;
            case OrderStatus::ERROR:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = 'late';
                $result['status'] = self::STATUS_ERROR;
                break;
            case OrderStatus::CANCELLED:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = 'late';
                $result['status'] = self::STATUS_CANCELLED;
                break;
            case OrderStatus::REFUNDED:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = 'late';
                $result['status'] = self::STATUS_REFUNDED;
                break;
        }
        return $result;
    }

    public function getOrderTypes()
    {
        // @todo don't hardcode path to images
        $result = '/bundles/rjpublic/images/icon-cash.png';
        
        return $result;
    }
}
