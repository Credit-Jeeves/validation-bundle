<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Lease
{
    /**
     * @Serializer\SerializedName("CurrentRent")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $currentRent;

    /**
     * @Serializer\SerializedName("ExpectedMoveInDate")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $expectedMoveInDate;

    /**
     * @Serializer\SerializedName("LeaseFromDate")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $leaseFromDate;

    /**
     * @Serializer\SerializedName("LeaseToDate")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $leaseToDate;

    /**
     * @Serializer\SerializedName("ActualMoveOut")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $actualMoveOut;

    /**
     * @Serializer\SerializedName("ResponsibleForLease")
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $responsibleForLease;

    /**
     * @return \DateTime
     */
    public function getActualMoveOut()
    {
        return $this->actualMoveOut;
    }

    /**
     * @param \DateTime $actualMoveOut
     */
    public function setActualMoveOut(\DateTime $actualMoveOut)
    {
        $this->actualMoveOut = $actualMoveOut;
    }

    /**
     * @return float
     */
    public function getCurrentRent()
    {
        return $this->currentRent;
    }

    /**
     * @param float $currentRent
     */
    public function setCurrentRent($currentRent)
    {
        $this->currentRent = $currentRent;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpectedMoveInDate()
    {
        return $this->expectedMoveInDate;
    }

    /**
     * @param mixed $expectedMoveInDate
     */
    public function setExpectedMoveInDate(\DateTime $expectedMoveInDate)
    {
        $this->expectedMoveInDate = $expectedMoveInDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getLeaseFromDate()
    {
        return $this->leaseFromDate;
    }

    /**
     * @param mixed $leaseFromDate
     */
    public function setLeaseFromDate(\DateTime $leaseFromDate)
    {
        $this->leaseFromDate = $leaseFromDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getLeaseToDate()
    {
        return $this->leaseToDate;
    }

    /**
     * @param mixed $leaseToDate
     */
    public function setLeaseToDate(\DateTime $leaseToDate)
    {
        $this->leaseToDate = $leaseToDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getResponsibleForLease()
    {
        return $this->responsibleForLease;
    }

    /**
     * @param \DateTime $responsibleForLease
     */
    public function setResponsibleForLease(\DateTime $responsibleForLease)
    {
        $this->responsibleForLease = $responsibleForLease;
    }
}
