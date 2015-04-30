<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("OpenItem")
 */
class OpenItem
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
     * @Serializer\SerializedName("OccuLastName")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $occuLastName;

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
