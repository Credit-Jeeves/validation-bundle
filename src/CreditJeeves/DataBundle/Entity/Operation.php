<?php
namespace CreditJeeves\DataBundle\Entity;

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
     * @Serializer\SerializedName("AccountId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    protected $accountId = null;

    /**
     * @Serializer\SerializedName("ArAccountId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    protected $arAccountId = null;

    /**
     * @Serializer\SerializedName("PropertyId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    protected $propertyId = null;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\SerializedName("Amount")
     * @Serializer\Type("float")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getFormatedAmount()
    {
        return number_format($this->getAmount(), 2, '.', '');
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
        return $this->getCreatedAt()->format('Y-m-d\TH:m:n');
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    public function getArAccountId()
    {
        return $this->arAccountId;
    }

    public function setArAccountId($arAccountId)
    {
        $this->arAccountId = $arAccountId;
        return $this;
    }

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
     * @Serializer\SerializedName("ChargeId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getChargeId()
    {
        return null;
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
        return (string)$this->getType();
    }

    public function getDaysLate()
    {
        $days = $this->getPaidFor()->diff($this->getCreatedAt())->format('%r%a');
        return $days;
    }
}
