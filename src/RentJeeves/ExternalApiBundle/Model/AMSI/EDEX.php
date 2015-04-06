<?php
namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("EDEX")
 */
class EDEX
{
    /**
     * @Serializer\SerializedName("propertyid")
     * @Serializer\Type("string")
     * @Serializer\Groups({"AMSI"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("leasestatus")
     * @Serializer\Type("string")
     * @Serializer\Groups({"AMSI"})
     * @Serializer\XmlElement(cdata=false)
     *
     * @var string
     */
    protected $leaseStatus = 'C';

    /**
     * @Serializer\SerializedName("includeprimaryaddress")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"AMSI"})
     *
     * @var string
     */
    protected $includePrimaryAddress = 1;

    /**
     * @Serializer\SerializedName("includecontactdetails")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"AMSI"})
     *
     * @var string
     */
    protected $includeContactDetails = 1;

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
}
