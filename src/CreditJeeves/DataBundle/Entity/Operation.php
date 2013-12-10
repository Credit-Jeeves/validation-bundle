<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Operation as Base;
use JMS\Serializer\Annotation as Serializer;

/**
 * Operation
 *
 * @ORM\Table(name="cj_operation")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OperationRepository")
 */
class Operation extends Base
{
    /**
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Notes")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("DateTime")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return DateTime
     */
    public function getNotes()
    {
        return $this->getCreatedAt();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("AccountId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("ArAccountId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
    public function getArAccountId()
    {
        return $this->arAccountId;
    }

    public function setArAccountId($arAccountId)
    {
        $this->arAccountId = $arAccountId;
        return $this;
    }
    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("PropertyId")
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\Type("integer")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return integer
     */
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
     * @param \CreditJeeves\DataBundle\Entity\Order $orders
     * @return Operation
     */
    public function setOrders($orders)
    {
        if (is_object($orders)) {
            $this->addOrder($orders);
            $orders->addOperation($this);
        }

        foreach ($orders as $order) {
            $this->addOrder($order);
            $order->addOperation($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getType();
    }
}
