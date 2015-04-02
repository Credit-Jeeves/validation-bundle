<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Occupant")
 */
class Occupant
{
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
     * @Serializer\SerializedName("OccuSeqNo")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuSeqNo;

    /**
     * @Serializer\SerializedName("OccuFirstName")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuFirstName;

    /**
     * @Serializer\SerializedName("OccuMiName")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuMiName;

    /**
     * @Serializer\SerializedName("OccuLastName")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuLastName;

    /**
     * @Serializer\SerializedName("Phone1No")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $phone1No;

    /**
     * @Serializer\SerializedName("Phone2No")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $phone2No;

    /**
     * @Serializer\SerializedName("Sex")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $sex;

    /**
     * @Serializer\SerializedName("MaritalStatus")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $maritalStatus;

    /**
     * @Serializer\SerializedName("ResponsibleFlag")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $responsibleFlag;

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
    public function getMaritalStatus()
    {
        return $this->maritalStatus;
    }

    /**
     * @param string $maritalStatus
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->maritalStatus = $maritalStatus;
    }

    /**
     * @return string
     */
    public function getOccuFirstName()
    {
        return $this->occuFirstName;
    }

    /**
     * @param string $occuFirstName
     */
    public function setOccuFirstName($occuFirstName)
    {
        $this->occuFirstName = $occuFirstName;
    }

    /**
     * @return string
     */
    public function getOccuLastName()
    {
        return $this->occuLastName;
    }

    /**
     * @param string $occuLastName
     */
    public function setOccuLastName($occuLastName)
    {
        $this->occuLastName = $occuLastName;
    }

    /**
     * @return string
     */
    public function getOccuMiName()
    {
        return $this->occuMiName;
    }

    /**
     * @param string $occuMiName
     */
    public function setOccuMiName($occuMiName)
    {
        $this->occuMiName = $occuMiName;
    }

    /**
     * @return string
     */
    public function getOccuSeqNo()
    {
        return $this->occuSeqNo;
    }

    /**
     * @param string $occuSeqNo
     */
    public function setOccuSeqNo($occuSeqNo)
    {
        $this->occuSeqNo = $occuSeqNo;
    }

    /**
     * @return string
     */
    public function getPhone1No()
    {
        return $this->phone1No;
    }

    /**
     * @param string $phone1No
     */
    public function setPhone1No($phone1No)
    {
        $this->phone1No = $phone1No;
    }

    /**
     * @return string
     */
    public function getPhone2No()
    {
        return $this->phone2No;
    }

    /**
     * @param string $phone2No
     */
    public function setPhone2No($phone2No)
    {
        $this->phone2No = $phone2No;
    }

    /**
     * @return string
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param string $propertyId
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
    public function getResponsibleFlag()
    {
        return $this->responsibleFlag;
    }

    /**
     * @param string $responsibleFlag
     */
    public function setResponsibleFlag($responsibleFlag)
    {
        $this->responsibleFlag = $responsibleFlag;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
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
}
