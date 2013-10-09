<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;

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
        // @todo don't hardcode path to images
        $result = '';// '/bundles/rjpublic/images/icon-cash.png';
        
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
        $result = 'N/A';
        $heartlands = $this->getHeartlands();
        if (count($heartlands) > 0) {
            $result = $heartlands->last()->getTransactionId();
        }
        return $result;
    }

    public function checkOrderProperties()
    {
        $operation = $this->getOperations()->last();
        $type = $operation->getType();
        switch ($type) {
            case OperationType::RENT:
                //echo $this->getStatus();
                $contract = $operation->getContract();
                
                $paidTo = $contract->getPaidTo();
                $interval = $this->getDiffDays($paidTo);
                $this->setDaysLate(0);//$interval);
                //echo $paidTo->format('Y-m-d').'->'.$interval."\n";
                break;
        }
//         $status = $this->getStatus();
//         $operation = $this->getOperations()->last();
//         $operationType = $operation->getType();
//         echo $operation->getType();
//          echo $this->getUser()->getFullName()."\n";
//          echo $this->getType()."\n";
//          echo $this->getStatus()."\n";
//          echo $this->getAmount()."\n";
//          echo "*************\n";
    }
}
