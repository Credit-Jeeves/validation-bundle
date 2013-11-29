<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 * @ORM\Table(name="cj_order")
 * @ORM\HasLifecycleCallbacks()
 * @Serializer\AccessorOrder("custom", custom = {
 *      "TotalAmount",
 *      "IsCash",
 *      "CheckNumber",
 *      "Date",
 *      "Notes",
 *      "IsCash",
 *      "PayerName",
 *      "operations"
 * })
 */
class Order extends BaseOrder
{
    use \RentJeeves\CoreBundle\Traits\DateCommon;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TotalAmount")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->getAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("IsCash")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return string
     */
    public function getIsCash()
    {
        if ($this->getType() === OrderType::CASH) {
            return true;
        }

        return false;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("CheckNumber")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return string
     */
    public function getCheckNumber()
    {
        if ($this->getIsCash()) {
            return null;
        }

        $checkNumber = $this->getType()." ".$this->getHeartlandTransactionId();
        return $checkNumber;
    }

    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->getUpdatedAt();
    }

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
        $property = $this->getContract()->getProperty();
        $unit = $this->getContract()->getUnit();
        $unitName = '';
        if ($unit) {
            $unitName = ' #'.$unit->getName();
        }
        $address = $property->getFullAddress().$unitName;

        return $address;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PayerName")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return DateTime
     */
    public function getPayerName()
    {
        $tenant = $this->getContract()->getTenant();
        return $tenant->getFullName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PostMonth")
     * @Serializer\Groups({"xmlBaseReport"})
     *
     * @return DateTime
     */
    public function getPostMonth()
    {
        $date = $this->getUpdatedAt();
        $daysLate = $this->getDaysLate();
        if ($daysLate < 0) {
            $date->modify('-'.$daysLate.' day');
        } elseif ($daysLate > 0) {
            $daysLate = $daysLate * -1;
            $date->modify($daysLate.' day');
        }

        return $date;
    }

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

    /**
     * @param bool $asString Defines whether to return a string or an array
     * @param string $glue A glue for string result
     * @return array|string
     */
    public function getHeartlandTransactionIds($asString = true, $glue = ', ')
    {
        $result = array();
        foreach ($this->getHeartlands() as $heartland) {
            $result[] = $heartland->getTransactionId();
        }

        if ($asString) {
            return implode($glue, $result);
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

    /**
     * @return \RentJeeves\DataBundle\Entity|null
     */
    public function getContract()
    {
        if ($operation = $this->getOperations()->last()) {
            return $operation->getContract();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasContract()
    {
        if ($this->getContract()) {
            return true;
        }

        return false;
    }

    /**
     * @deprecated Will be removed in v2.2
     * @return mixed
     */
    public function getTenant()
    {
        return $this->getContract()->getTenant();
    }

    /**
     * @deprecated Will be removed in v2.2
     * @return mixed
     */
    public function getGroup()
    {
        return $this->getContract()->getGroup();
    }

    /**
     * @deprecated Will be removed in v2.2
     * @return mixed
     */
    public function getHolding()
    {
        return $this->getContract()->getHolding();
    }

    /**
     * @return string|null
     */
    public function getGroupName()
    {
        if ($contract = $this->getContract()) {
            return $contract->getGroup()->getName();
        }

        return null;
    }

    public function getAvailableOrderStatuses()
    {
        return OrderStatus::all();
    }
}
