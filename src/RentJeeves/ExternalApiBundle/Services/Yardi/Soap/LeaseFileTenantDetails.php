<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileTenantDetails
{
    /**
     * @Serializer\SerializedName("Name")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenantName")
     */
    protected $name;

    /**
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\LeaseFileTenantAddress")
     */
    protected $address;

    /**
     * @Serializer\SerializedName("Email")
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $name
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

}
