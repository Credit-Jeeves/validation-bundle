<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("RecurringCharge")
 */
class RecurringCharge
{
    const FREQUENCY_MONTH = 'M';
    const RENT_INCOME_CODE_ID = 'RENT';

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
     * @Serializer\SerializedName("IncCodeDesc")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $incCodeDesc;

    /**
     * @Serializer\SerializedName("IncCode")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $incCode;

    /**
     * @Serializer\SerializedName("ChargeBeginDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     *
     * @var \DateTime
     */
    protected $chargeBeginDate;

    /**
     * @Serializer\SerializedName("ChargeEndDate")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     *
     * @var \DateTime
     */
    protected $chargeEndDate;

    /**
     * @Serializer\SerializedName("FreqCode")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $freqCode;

    /**
     * @Serializer\SerializedName("Amount")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("float")
     *
     * @var float
     */
    protected $amount;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return \DateTime
     */
    public function getChargeBeginDate()
    {
        return $this->chargeBeginDate;
    }

    /**
     * @param \DateTime $chargeBeginDate
     */
    public function setChargeBeginDate($chargeBeginDate)
    {
        $this->chargeBeginDate = $chargeBeginDate;
    }

    /**
     * @return \DateTime
     */
    public function getChargeEndDate()
    {
        return $this->chargeEndDate;
    }

    /**
     * @param \DateTime $chargeEndDate
     */
    public function setChargeEndDate($chargeEndDate)
    {
        $this->chargeEndDate = $chargeEndDate;
    }

    /**
     * @return string
     */
    public function getFreqCode()
    {
        return $this->freqCode;
    }

    /**
     * @param string $freqCode
     */
    public function setFreqCode($freqCode)
    {
        $this->freqCode = $freqCode;
    }

    /**
     * @return string
     */
    public function getIncCodeDesc()
    {
        return $this->incCodeDesc;
    }

    /**
     * @param string $incCodeDesc
     */
    public function setIncCodeDesc($incCodeDesc)
    {
        $this->incCodeDesc = $incCodeDesc;
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
     * @return string
     */
    public function getIncCode()
    {
        return $this->incCode;
    }

    /**
     * @param string $incCode
     */
    public function setIncCode($incCode)
    {
        $this->incCode = $incCode;
    }
}
