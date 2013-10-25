<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 * @ORM\Table(name="cj_order")
 * @ORM\HasLifecycleCallbacks()
 */
class Order extends BaseOrder
{
    use \RentJeeves\CoreBundle\Traits\DateCommon;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updated_at = new \DateTime();
    }

    public function setOperations($operations)
    {
        if (is_object($operations)) {
            $this->addOperation($operations);
        }

        foreach ($operations as $operation) {
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
        $result['finish'] = '--';
        $result['style'] = 'contract-pending';
        $result['icon'] = $this->getOrderTypes();
        $status = $this->getStatus();
        $result['status'] = 'order.status.text.'.$status;
        switch ($status) {
            case OrderStatus::COMPLETE:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = '';
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
                $result['style'] = 'late';
                break;
        }
        return $result;
    }

    public function getOrderTypes()
    {
        $type = $this->getType();
        switch ($type) {
            case OrderType::HEARTLAND_CARD:
                $result = 'credit-card';
                break;
            case OrderType::HEARTLAND_BANK:
                $result = 'e-check';
                break;
            case OrderType::CASH:
                $result = 'cash';
                break;
            default:
                $result = '';
                break;
        }
        return $result;
    }

    public function getOperationType()
    {
        $result = array();
        $operations = $this->getOperations();
        foreach ($operations as $operation) {
            $type = $operation->getType();
            if (!in_array($type, $result)) {
                $result[] = $type;
            }
        }
        return implode(', ', $result);
    }

    public function getHeartlandTransactionId()
    {
        $result = 0;
        $heartlands = $this->getHeartlands();
        if (count($heartlands) > 0) {
            $result = $heartlands->last()->getTransactionId();
        }
        return $result;
    }

    public function getHeartlandErrorMessage()
    {
        $result = '';
        $heartlands = $this->getHeartlands();
        if (count($heartlands) > 0) {
            $result = $heartlands->last()->getMessages();
        }
        return $result;
    }

    public function countDaysLate()
    {
        $operation = $this->getOperations()->last();
        $type = OperationType::REPORT;
        $type = $operation ? $operation->getType() : $type;
        switch ($type) {
            case OperationType::RENT:
                $contract = $operation->getContract();
                $paidTo = $contract->getPaidTo();
                $interval = $this->getDiffDays($paidTo);
                $this->setDaysLate($interval);
                break;
        }
    }

    public function checkOrderProperties()
    {
        $operations = $this->getOperations();
        $orderAmount = $this->getAmount();
        foreach ($operations as $operation) {
            $type = $operation->getType();
            switch ($type) {
                case OperationType::RENT:
                    $status = $this->getStatus();
                    if ($status == OrderStatus::COMPLETE) {
                        $contract = $operation->getContract();
                        $paidTo = $contract->getPaidTo();
                        $interval = $this->getDiffDays($paidTo);
                        $this->setDaysLate($interval);
                    }
                    break;
                case OperationType::REPORT:
                    // Nothing to do now
                    break;
            }
        }
    }

    public function getContract()
    {
        return $this->getOperations()->last()->getContract();
    }
    
    public function getTenant()
    {
        return $this->getContract()->getTenant();
    }

    public function getGroup()
    {
        return $this->getContract()->getGroup();
    }

    public function getHolding()
    {
        return $this->getContract()->getHolding();
    }
}
