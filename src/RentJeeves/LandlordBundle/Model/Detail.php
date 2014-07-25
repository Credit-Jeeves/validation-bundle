<?php

namespace RentJeeves\LandlordBundle\Model;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\UnitMapping;
use Symfony\Component\Form\Form;
use DateTime;

class Detail
{
    /**
     * @var float
     */
    protected $amount = null;

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
     * @var DateTime
     */
    protected $notes = null;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"xmlReport"})
     * @Serializer\SerializedName("Amount")
     * @Serializer\Type("float")
     * @Serializer\XmlElement(cdata=false)
     *
     * @return string
     */
    public function getAmountFormatted()
    {
        return number_format($this->amount, 2, '.', '');
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
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
    public function getNotesFormatted()
    {
        return $this->notes->format('Y-m-d\TH:i:s');
    }

    public function setNotes(DateTime $notes)
    {
        $this->notes = $notes;
        return;
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
}
