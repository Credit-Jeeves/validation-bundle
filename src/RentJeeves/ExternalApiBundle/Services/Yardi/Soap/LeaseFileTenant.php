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
     * @return mixed
     */
    public function getPersonDetails()
    {
        return $this->personDetails;
    }

    /**
     * @param mixed $personDetails
     */
    public function setPersonDetails($personDetails)
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
