<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Lease")
 */
class Lease
{
    /**
     * @Serializer\SerializedName("Occupant")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\AMSI\Occupant>")
     * @Serializer\XmlList(inline = true, entry = "Occupant")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"AMSI"})
     *
     * @var array
     */
    protected $occupants;

    /**
     * @Serializer\SerializedName("OpenItem")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\AMSI\OpenItem>")
     * @Serializer\XmlList(inline = true, entry = "Occupant")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"AMSI"})
     *
     * @var array
     */
    protected $openItems;

    /**
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\AMSI\Address")
     * @Serializer\Groups({"AMSI"})
     *
     * @var Address
     */
    protected $address;

    /**
     * @Serializer\SerializedName("PropertyId")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("BldgID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $bldgId;

    /**
     * @Serializer\SerializedName("UnitID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $unitId;

    /**
     * @Serializer\SerializedName("ResiID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $resiId;

    /**
     * @Serializer\SerializedName("Name")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\SerializedName("Email")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $email;

    /**
     * @Serializer\SerializedName("EndBalance")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $endBalance;

    /**
     * @Serializer\SerializedName("occustatuscode")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuStatusCode;

    /**
     * @Serializer\SerializedName("occustatuscodedescription")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuStatusCodeDescription;

    /**
     * @Serializer\SerializedName("MoveInDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $moveInDate;

    /**
     * @Serializer\SerializedName("CreditStatus")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $creditStatus;

    /**
     * @Serializer\SerializedName("CreditStatusDescription")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $creditStatusDescription;

    /**
     * @Serializer\SerializedName("ExternalReferenceID")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $externalReferenceId;

    /**
     * @Serializer\SerializedName("AllowAutoEnroll")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var float
     */
    protected $allowAutoEnroll;

    /**
     * @Serializer\SerializedName("BlockPaymentAccess")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $blockPaymentAccess;

    /**
     * @Serializer\SerializedName("BlockAllAccess")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $blockAllAccess;

    /**
     * @Serializer\SerializedName("SecurityDepositOnHand")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $securityDepositOnHand;

    /**
     * @Serializer\SerializedName("leasebegindate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $leaseBeginDate;

    /**
     * @Serializer\SerializedName("leaseenddate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var float
     */
    protected $leaseEndDate;

    /**
     * @Serializer\SerializedName("rentamount")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $rentAmount;

    /**
     * @Serializer\SerializedName("ApplicationDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $applicationDate;

    /**
     * @Serializer\SerializedName("LeaseSignDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $leaseSignDate;

    /**
     * @Serializer\SerializedName("QuotedRent")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $quotedRent;

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return array
     */
    public function getOccupants()
    {
        return $this->occupants;
    }

    /**
     * @param array $occupant
     */
    public function setOccupants($occupant)
    {
        $this->occupants = $occupant;
    }

    /**
     * @return array
     */
    public function getOpenItems()
    {
        return $this->openItems;
    }

    /**
     * @param array $openItem
     */
    public function setOpenItems($openItem)
    {
        $this->openItems = $openItem;
    }

    /**
     * @return string
     */
    public function getBldgId()
    {
        return $this->bldgId;
    }

    /**
     * @param string $bldgId
     */
    public function setBldgId($bldgId)
    {
        $this->bldgId = $bldgId;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param mixed $propertyId
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return string
     */
    public function getResiId()
    {
        return $this->resiId;
    }

    /**
     * @param string $resiId
     */
    public function setResiId($resiId)
    {
        $this->resiId = $resiId;
    }

    /**
     * @return string
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * @param string $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * @return float
     */
    public function getAllowAutoEnroll()
    {
        return $this->allowAutoEnroll;
    }

    /**
     * @param float $allowAutoEnroll
     */
    public function setAllowAutoEnroll($allowAutoEnroll)
    {
        $this->allowAutoEnroll = $allowAutoEnroll;
    }

    /**
     * @return string
     */
    public function getApplicationDate()
    {
        return $this->applicationDate;
    }

    /**
     * @param string $applicationDate
     */
    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;
    }

    /**
     * @return string
     */
    public function getBlockAllAccess()
    {
        return $this->blockAllAccess;
    }

    /**
     * @param string $blockAllAccess
     */
    public function setBlockAllAccess($blockAllAccess)
    {
        $this->blockAllAccess = $blockAllAccess;
    }

    /**
     * @return string
     */
    public function getBlockPaymentAccess()
    {
        return $this->blockPaymentAccess;
    }

    /**
     * @param string $blockPaymentAccess
     */
    public function setBlockPaymentAccess($blockPaymentAccess)
    {
        $this->blockPaymentAccess = $blockPaymentAccess;
    }

    /**
     * @return string
     */
    public function getCreditStatus()
    {
        return $this->creditStatus;
    }

    /**
     * @param string $creditStatus
     */
    public function setCreditStatus($creditStatus)
    {
        $this->creditStatus = $creditStatus;
    }

    /**
     * @return string
     */
    public function getCreditStatusDescription()
    {
        return $this->creditStatusDescription;
    }

    /**
     * @param string $creditStatusDescription
     */
    public function setCreditStatusDescription($creditStatusDescription)
    {
        $this->creditStatusDescription = $creditStatusDescription;
    }

    /**
     * @return float
     */
    public function getEndBalance()
    {
        return $this->endBalance;
    }

    /**
     * @param float $endBalance
     */
    public function setEndBalance($endBalance)
    {
        $this->endBalance = $endBalance;
    }

    /**
     * @return string
     */
    public function getExternalReferenceId()
    {
        return $this->externalReferenceId;
    }

    /**
     * @param string $externalReferenceId
     */
    public function setExternalReferenceId($externalReferenceId)
    {
        $this->externalReferenceId = $externalReferenceId;
    }

    /**
     * @return string
     */
    public function getLeaseSignDate()
    {
        return $this->leaseSignDate;
    }

    /**
     * @param string $leaseSignDate
     */
    public function setLeaseSignDate($leaseSignDate)
    {
        $this->leaseSignDate = $leaseSignDate;
    }

    /**
     * @return string
     */
    public function getLeaseBeginDate()
    {
        return $this->leaseBeginDate;
    }

    /**
     * @param string $leasebegindate
     */
    public function setLeaseBeginDate($leasebegindate)
    {
        $this->leaseBeginDate = $leasebegindate;
    }

    /**
     * @return float
     */
    public function getLeaseEndDate()
    {
        return $this->leaseEndDate;
    }

    /**
     * @param float $leaseenddate
     */
    public function setLeaseEndDate($leaseenddate)
    {
        $this->leaseEndDate = $leaseenddate;
    }

    /**
     * @return string
     */
    public function getMoveInDate()
    {
        return $this->moveInDate;
    }

    /**
     * @param string $moveInDate
     */
    public function setMoveInDate($moveInDate)
    {
        $this->moveInDate = $moveInDate;
    }

    /**
     * @return string
     */
    public function getOccuStatusCode()
    {
        return $this->occuStatusCode;
    }

    /**
     * @param string $occuStatusCode
     */
    public function setOccuStatusCode($occuStatusCode)
    {
        $this->occuStatusCode = $occuStatusCode;
    }

    /**
     * @return string
     */
    public function getOccuStatusCodeDescription()
    {
        return $this->occuStatusCodeDescription;
    }

    /**
     * @param string $occuStatusCodeDescription
     */
    public function setOccuStatusCodeDescription($occuStatusCodeDescription)
    {
        $this->occuStatusCodeDescription = $occuStatusCodeDescription;
    }

    /**
     * @return float
     */
    public function getQuotedRent()
    {
        return $this->quotedRent;
    }

    /**
     * @param float $quotedRent
     */
    public function setQuotedRent($quotedRent)
    {
        $this->quotedRent = $quotedRent;
    }

    /**
     * @return float
     */
    public function getRentAmount()
    {
        return $this->rentAmount;
    }

    /**
     * @param float $rentamount
     */
    public function setRentAmount($rentamount)
    {
        $this->rentAmount = $rentamount;
    }

    /**
     * @return float
     */
    public function getSecurityDepositOnHand()
    {
        return $this->securityDepositOnHand;
    }

    /**
     * @param float $securityDepositOnHand
     */
    public function setSecurityDepositOnHand($securityDepositOnHand)
    {
        $this->securityDepositOnHand = $securityDepositOnHand;
    }
}
