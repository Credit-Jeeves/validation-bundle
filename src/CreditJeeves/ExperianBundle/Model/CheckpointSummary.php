<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("CheckpointSummary")
 */
class CheckpointSummary
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("OFACValidationResult")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var int
     */
    protected $OFACValidationResult;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("DateOfBirthMatch")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var int
     */
    protected $dateOfBirthMatch;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("HighRiskAddrCode")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var string
     */
    protected $highRiskAddrCode;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("AddrResMatches")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var string
     */
    protected $addrResMatches;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PhnResMatches")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var string
     */
    protected $phnResMatches;

    /**
     * @param int $OFACValidationResult
     */
    public function setOFACValidationResult($OFACValidationResult)
    {
        $this->OFACValidationResult = $OFACValidationResult;
    }

    /**
     * @return int
     */
    public function getOFACValidationResult()
    {
        return $this->OFACValidationResult;
    }

    /**
     * @param int $dateOfBirthMatch
     */
    public function setDateOfBirthMatch($dateOfBirthMatch)
    {
        $this->dateOfBirthMatch = $dateOfBirthMatch;
    }

    /**
     * @return int
     */
    public function getDateOfBirthMatch()
    {
        return $this->dateOfBirthMatch;
    }

    /**
     * @param string $highRiskAddrCode
     */
    public function setHighRiskAddrCode($highRiskAddrCode)
    {
        $this->highRiskAddrCode = $highRiskAddrCode;
    }

    /**
     * @return string
     */
    public function getHighRiskAddrCode()
    {
        return $this->highRiskAddrCode;
    }

    /**
     * @param string $addrResMatches
     */
    public function setAddrResMatches($addrResMatches)
    {
        $this->addrResMatches = $addrResMatches;
    }

    /**
     * @return string
     */
    public function getAddrResMatches()
    {
        return $this->addrResMatches;
    }

    /**
     * @param string $phnResMatches
     */
    public function setPhnResMatches($phnResMatches)
    {
        $this->phnResMatches = $phnResMatches;
    }

    /**
     * @return string
     */
    public function getPhnResMatches()
    {
        return $this->phnResMatches;
    }
}
