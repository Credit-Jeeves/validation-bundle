<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileTenantName 
{
    /**
     * @Serializer\SerializedName("FirstName")
     * @Serializer\Type("string")
     */
    protected $firstName;

    /**
     * @Serializer\SerializedName("LastName")
     * @Serializer\Type("string")
     */
    protected $lastName;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
} 
