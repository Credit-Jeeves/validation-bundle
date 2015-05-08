<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Operation as Base;
use JMS\Serializer\Annotation as Serializer;

/**
 * Operation
 *
 * @todo split to types
 *
 * @ORM\Table(name="cj_operation")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OperationRepository")
 */
class Operation extends Base
{
    /**
     * Date time of actual payment transaction with Heartland
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Date")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return \DateTime
     */
    public function getActualPaymentTransactionDate()
    {
        return $this->getOrder()->getCreatedAt()->format('Y-m-d\TH:i:s');
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
        $order = $this->getOrder();
        $residentMapping = $order->getContract()->getTenant()->getResidentsMapping();
        /** @var ResidentMapping $mapping */
        foreach ($residentMapping as $mapping) {
            if ($mapping->getHolding()->getId() == $order->getContract()->getHolding()->getId()) {
                return $mapping->getResidentId();
            }
        }

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
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return number_format($this->amount, 2, '.', '');
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
        if ($this->getOrder()->getType() === OrderType::CASH) {
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
        if ($this->getOrder()->getType() === OrderType::HEARTLAND_CARD) {
            $code = 'PMTCRED';
        } elseif ($this->getOrder()->getType() === OrderType::HEARTLAND_BANK) {
            $code = 'PMTCHECK';
        } elseif ($this->getOrder()->getType() === OrderType::CASH) {
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
        $order = $this->getOrder();

        if (!$contract = $order->getContract()) {
            return null;
        }
        $property = $contract->getProperty();
        if (!$property) {
            return null;
        }

        $unit = $contract->getUnit();
        $unitName = '';
        if ($unit && !$property->isSingle()) {
            $unitName = ' #'.$unit->getName();
        }
        $address = $property->getFullAddress().$unitName;

        return $address;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("BatchId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string | integer
     */
    public function getBatchId()
    {
        return 0;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PostMonth")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("string")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return \DateTime
     */
    public function getPostMonth()
    {
        $paidFor = $this->getPaidFor();

        return $paidFor ? $paidFor->format('Y-m-d\TH:i:s') : '';
    }

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
     * Add orders
     *
     * @param \CreditJeeves\DataBundle\Entity\Order $order
     *
     * @return Operation
     */
    public function setOrder(\CreditJeeves\DataBundle\Entity\Order $order)
    {
        parent::setOrder($order);
        if (!$order->getOperations()->contains($this)) {
            $order->addOperation($this);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getType();
    }

    public function getDaysLate()
    {
        $days = $this->getPaidFor()->diff($this->getCreatedAt())->format('%r%a');

        return $days;
    }

    protected $reversalOrderTypes = [
        OrderStatus::RETURNED,
        OrderStatus::REFUNDED,
    ];

    public function getHeartlandTransactionId($original = true)
    {
        $order = $this->getOrder();

        if ($order) {
            if ($original && $trans = $order->getCompleteTransaction()) {
                return  $trans->getTransactionId();
            }

            return $order->getHeartlandTransactionId();
        }

        return null;
    }
}
