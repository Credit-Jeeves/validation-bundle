<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileTenant
{
    /**
     * @Serializer\SerializedName("PersonDetails")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenantDetails")
     */
    protected $personDetails;

    /**
     * @Serializer\SerializedName("DateOfBirth")
     * @Serializer\Type("string")
     */
    protected $dateOfBirth;

    /**
     * @return LeaseFileTenantDetails
     */
    public function getPersonDetails()
    {
        return $this->personDetails;
    }

    /**
     * @param LeaseFileTenantDetails $personDetails
     */
    public function setPersonDetails(LeaseFileTenantDetails $personDetails)
    {
        $this->personDetails = $personDetails;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }
}
