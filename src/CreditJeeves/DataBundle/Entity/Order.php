<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Order as BaseOrder;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Heartland;
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
     * @Serializer\SerializedName("PropertyId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * It's not property ID from DB, it's property id from user form
     * For generate correct report xml
     *
     * @return integer
     */
    protected $propertyId = null;


    public function getPropertyId()
    {
        return $this->propertyId;
    }

    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;

        return $this;
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Unit")
     * @Serializer\Groups({"csvReport"})
     *
     * @return string
     */
    public function getUnitName()
    {
        $unitName = '';
        $contract = $this->getContract();
        if (!$contract) {
            return $unitName;
        }

        $unit = $contract->getUnit();

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
     * @Serializer\Groups({"xmlReport", "csvReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getActualPaymentTransactionDate()
    {
        return $this->getUpdatedAt()->format('Y-m-d\TH:m:n');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Id")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getReportId()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("CashAccountId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getCashAccountId()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PersonId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getPersonId()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("HasOpenPrepayDetails")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getHasOpenPrepayDetails()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PaymentType")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getPaymentType()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UnitId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getUnitId()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_1")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields1()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_2")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields2()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_3")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields3()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_4")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields4()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_5")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields5()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_6")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields6()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_7")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields7()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("UserDefinedFields_8")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getUserDefinedFields8()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DateCreated")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getDateCreated()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("DateLastModified")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getDateLastModified()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("TotalAmount")
     * @Serializer\Groups({"xmlReport", "csvReport"})
     * @Serializer\Type("double")
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
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("IsCash")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("boolean")
     * @Serializer\XmlElement(cdata=false)
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
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getCheckNumber()
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

        return sprintf('%s %d', $code, $this->getHeartlandTransactionId());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Notes")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getNotes()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $property = $contract->getProperty();
        if (!$property) {
            return null;
        }

        $unit = $contract->getUnit();
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
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getPayerName()
    {
        if (!$contract = $this->getContract()) {
            return null;
        }
        $tenant = $contract->getTenant();
        if (!$tenant) {
            return null;
        }

        return $tenant->getFullName();
    }

    /**
     * @throws RuntimeException
     *
     * @return Operation
     */
    public function getRentOperation()
    {
        $operationCollection = $this->getOperations()
            ->filter(function(Operation $operation) {
                    if (OperationType::RENT == $operation->getType()) {
                        return true;
                    }
                    return false;
                }
            );
        if (1 < $operationCollection->count()) {
            throw new RuntimeException("Order has more than ONE 'RENT' operation");
        }
        if (0 == $operationCollection->count()) {
            throw new RuntimeException("Order must has ONE 'RENT' operation");
        }
        return $operationCollection->last();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PostMonth")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getPostMonth()
    {
        return $this->getRentOperation()->getPaidFor()->format('Y-m-d\TH:m:n');
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
            case OrderStatus::PENDING:
                $result['finish'] = $this->getUpdatedAt()->format('m/d/Y');
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
        /** @var Operation $operation */
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
        return OrderStatus::getManualAvailableToSet($this->getStatus());
    }

    public function __toString()
    {
        return (string)$this->getId();
    }
}
