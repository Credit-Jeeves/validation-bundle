<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;
use DateTime;
use RuntimeException;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderRepository")
 * @ORM\Table(name="cj_order")
 * @ORM\HasLifecycleCallbacks()
 */
class Order extends BaseOrder
{
    use \RentJeeves\CoreBundle\Traits\DateCommon;

    /**
     * @return DateTime
     */
    public function getPostMonth()
    {
        $paidFor = null;
        /** @var Operation $rentOperation */
        foreach ($this->getRentOperations() as $rentOperation) {
            if (!$paidFor || $paidFor < $rentOperation->getPaidFor()) {
                $paidFor = $rentOperation->getPaidFor();
            }
        }

        return $paidFor ? $paidFor->format('Y-m-d\TH:i:s') : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Property")
     * @Serializer\Groups({"csvReport"})
     *
     * @return string
     */
    public function getPropertyAddress()
    {
        $contract = $this->getContract();
        if (!$contract) {
            return null;
        }

        $property = $contract->getProperty();

        if (!$property) {
            return null;
        }

        return $property->getFullAddress();
    }

    /**
     * @return null | Unit
     */
    public function getUnit()
    {
        $contract = $this->getContract();

        if (!$contract) {
            return null;
        }

        $unit = $contract->getUnit();

        return $unit;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Unit")
     * @Serializer\Groups({"csvReport"})
     *
     * @return string
     */
    public function getUnitName()
    {
        $unitName = '';

        $unit = $this->getUnit();

        if ($unit) {
            $unitName = $unit->getName();
        }

        return $unitName;
    }

    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"csvReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getActualPaymentTransactionDate()
    {
        return $this->getCreatedAt()->format('Y-m-d\TH:i:s');
    }


    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return DateTime
     */
    public function getPaymentDateForPromas()
    {
        return $this->getCreatedAt()->format('Y-m-d');
    }

    /**
     * External UNIT ID for Promas report
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UNIT ID")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getExternalUnitId()
    {
        $unit = $this->getUnit();
        if ($unit) {
            return $unit->getUnitMapping()->getExternalUnitId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TotalAmount")
     * @Serializer\Groups({"csvReport", "promasReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return number_format($this->getSum(), 2, '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Memo")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getPromasMemo()
    {
        return sprintf('Trans #%s Batch #%s', $this->getHeartlandTransactionId(), $this->getHeartlandBatchId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Tenant ID")
     * @Serializer\Groups({"promasReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getTenantExternalId()
    {
        if ($residentMapping = $this->getContract()->getTenant()->getResidentsMapping()->first()) {
            return $residentMapping->getResidentId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("First_Name")
     * @Serializer\Groups({"csvReport"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getFirstNameTenant()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $tenant = $contract->getTenant();

        if (!$tenant) {
            return null;
        }

        return $tenant->getFirstName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Last_Name")
     * @Serializer\Groups({"csvReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getLastNameTenant()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $tenant = $contract->getTenant();

        if (!$tenant) {
            return null;
        }
        return $tenant->getLastName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Code")
     * @Serializer\Groups({"csvReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getCode()
    {
        if ($this->getType() === OrderType::HEARTLAND_CARD) {
            $code = 'PMTCRED';
        } elseif ($this->getType() === OrderType::HEARTLAND_BANK) {
            $code = 'PMTCHECK';
        } elseif ($this->getType() === OrderType::CASH) {
            $code = 'EXTERNAL';
        } else {
            $code = '';
        }

        return $code;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Description")
     * @Serializer\Groups({"csvReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getDescription()
    {
        return sprintf(
            '%s #%s %s %d',
            $this->getPropertyAddress(),
            $this->getUnitName(),
            $this->getCode(),
            $this->getHeartlandTransactionId()
        );
    }

    /**
     * @return string|null
     */
    public function getGroupName()
    {
        $contract = $this->getContract();
        if ($contract && ($group = $contract->getGroup())) {
            return $group->getName();
        }

        return null;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRentOperations()
    {
        return $this->getOperations()
            ->filter(
                function (Operation $operation) {
                    if (OperationType::RENT == $operation->getType()) {
                        return true;
                    }
                    return false;
                }
            );
    }

    /**
     * @return Operation|null
     */
    public function getOtherOperation()
    {
        $operationCollection = $this->getOperations()
            ->filter(
                function (Operation $operation) {
                    if (OperationType::OTHER == $operation->getType()) {
                        return true;
                    }
                    return false;
                }
            );

        if (0 == $operationCollection->count()) {
            return null;
        }

        return $operationCollection->last();
    }

    public function getRentAmount()
    {
        $result = 0;
        foreach ($this->getRentOperations() as $operation) {
            $result += $operation->getAmount();
        }

        return number_format($result, 2, '.', '');
    }

    public function getOtherAmount()
    {
        $result = 0;
        if ($this->getOtherOperation()) {
            $result = $this->getOtherOperation()->getAmount();
        }

        return number_format($result, 2, '.', '');
    }



    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $return = parent::addOperation($operation);
        if (!$operation->getOrder()) {
            $operation->setOrder($this);
        }
        return $return;
    }

    public function setOperations($operations)
    {
        /** @var Operation $operation */
        foreach ($operations as $operation) {
            $this->addOperation($operation);
        }

        return $this;
    }

    /**
     * @Serializer\Groups({"payment"})
     * @Serializer\HandlerCallback("json", direction = "serialization")
     *
     * @return array
     */
    public function getItem()
    {
        $result = array();
        /** @var Contract $contract */
        $contract = $this->getOperations()->last()->getContract();
        $result['amount'] = $this->getSum(); //TODO check. May be it must be operation getAmount()
        $result['tenant'] = $contract? $contract->getTenant()->getFullName() : '';
        $result['address'] = $contract? $contract->getRentAddress($contract->getProperty(), $contract->getUnit()) : '';
        $result['start'] = $this->getCreatedAt()->format('m/d/Y');
        $result['finish'] = '--';
        $result['style'] = $this->getOrderStatusStyle();
        $result['icon'] = $this->getOrderTypes();
        $status = $this->getStatus();
        $result['status'] = 'order.status.text.'.$status;
        switch ($status) {
            case OrderStatus::COMPLETE:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
                break;
            case OrderStatus::PENDING:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $result['finish'] = $this->getCreatedAt()->format('m/d/Y');
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

    protected function getOrderStatusStyle()
    {
        switch ($this->getStatus()) {
            case OrderStatus::COMPLETE:
                $style = '';
                break;
            case OrderStatus::ERROR:
            case OrderStatus::CANCELLED:
            case OrderStatus::REFUNDED:
            case OrderStatus::RETURNED:
                $style = 'late';
                break;
            default:
                $style = 'contract-pending';
        }

        return $style;
    }

    /**
     * @return array
     */
    public function getTenantPayment()
    {
        $result = array();
        $result['status'] = $this->getStatus();
        if ($this->getStatus() == OrderStatus::ERROR) {
            $result['errorMessage'] = $this->getHeartlandErrorMessage();
        }
        $result['style'] = $this->getOrderStatusStyle();
        $result['date'] = $this->getCreatedAt()->format('m/d/Y');
        $result['property'] = $this->getContract()? $this->getContract()->getRentAddress() : 'N/A';

        $result['rent'] = $this->getRentAmount();
        $result['other'] = $this->getOtherAmount();
        $result['total'] = $this->getTotalAmount();
        $result['type'] = $this->getOrderTypes();

        return $result;
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

    public function getHeartlandBatchId()
    {
        $heartlands = $this->getHeartlands();
        if (count($heartlands) > 0) {
            return $heartlands->last()->getBatchId();
        }

        return null;
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
     *
     * @return array|string
     */
    public function getHeartlandTransactionIds($asString = true, $glue = ', ')
    {
        $result = array();
        /** @var Heartland $heartland */
        foreach ($this->getHeartlands() as $heartland) {
            $result[] = $heartland->getTransactionId();
        }

        if ($asString) {
            return implode($glue, $result);
        }

        return $result;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract | null
     */
    public function getContract()
    {
        /** @var Operation $operation */
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

    public function getAvailableOrderStatuses()
    {
        return OrderStatus::getManualAvailableToSet($this->getStatus());
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    public function getFee()
    {
        $fee = 0;
        switch ($this->getType()) {
            case OrderType::HEARTLAND_CARD:
                $fee = round($this->getSum() * (float)$this->getContract()->getDepositAccount()->getFeeCC()) / 100;
                break;
            case OrderType::HEARTLAND_BANK:
                $fee = (float)$this->getContract()->getDepositAccount()->getFeeACH();
                break;
        }
        return $fee;
    }

    public function getReversalDescription()
    {
        return '';
    }
}
