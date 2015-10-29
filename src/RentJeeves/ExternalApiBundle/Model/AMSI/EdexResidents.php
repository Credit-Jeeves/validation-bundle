<?php
namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("EDEX")
 */
class EdexResidents
{
    /**
     * @Serializer\SerializedName("propertyid")
     * @Serializer\Type("string")
     * @Serializer\Groups({"GetPropertyResidents", "GetPropertyUnits"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("leasestatus")
     * @Serializer\Type("string")
     * @Serializer\Groups({"GetPropertyResidents"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $leaseStatus = 'C';

    /**
     * @Serializer\SerializedName("includeprimaryaddress")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"GetPropertyResidents"})
     *
     * @var int
     */
    protected $includePrimaryAddress = 1;

    /**
     * @Serializer\SerializedName("includecontactdetails")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"GetPropertyResidents"})
     *
     * @var int
     */
    protected $includeContactDetails = 1;

    /**
     * @Serializer\SerializedName("includerecurringcharges")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"GetPropertyResidents"})
     *
     * @var int
     */
    protected $includeRecurringCharges = 0;

    /**
     * @Serializer\SerializedName("includeallstatuses")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"GetPropertyUnits"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var int
     */
    protected $includeAllStatuses = 1;

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
    public function getIncludeContactDetails()
    {
        return $this->includeContactDetails;
    }

    /**
     * @param string $includeContactDetails
     */
    public function setIncludeContactDetails($includeContactDetails)
    {
        $this->includeContactDetails = $includeContactDetails;
    }

    /**
     * @return string
     */
    public function getIncludePrimaryAddress()
    {
        return $this->includePrimaryAddress;
    }

    /**
     * @param string $includePrimaryAddress
     */
    public function setIncludePrimaryAddress($includePrimaryAddress)
    {
        $this->includePrimaryAddress = $includePrimaryAddress;
    }

    /**
     * @return string
     */
    public function getLeaseStatus()
    {
        return $this->leaseStatus;
    }

    /**
     * @param string $leaseStatus
     */
    public function setLeaseStatus($leaseStatus)
    {
        $this->leaseStatus = $leaseStatus;
    }

    /**
     * @return int
     */
    public function getIncludeRecurringCharges()
    {
        return $this->includeRecurringCharges;
    }

    /**
     * @param int $includeRecurringCharges
     */
    public function setIncludeRecurringCharges($includeRecurringCharges)
    {
        $this->includeRecurringCharges = $includeRecurringCharges;
    }

    /**
     * @return int
     */
    public function getIncludeAllStatuses()
    {
        return $this->includeAllStatuses;
    }

    /**
     * @param int $includeAllStatuses
     */
    public function setIncludeAllStatuses($includeAllStatuses)
    {
        $this->includeAllStatuses = $includeAllStatuses;
    }
}
