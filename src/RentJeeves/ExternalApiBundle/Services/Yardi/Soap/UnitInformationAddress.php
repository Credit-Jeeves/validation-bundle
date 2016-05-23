<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class UnitInformationAddress
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("Address1")
     * @Serializer\Type("string")
     */
    protected $address1;

    /**
     * @var string
     *
     * @Serializer\SerializedName("City")
     * @Serializer\Type("string")
     */
    protected $city;

    /**
     * @var string
     *
     * @Serializer\SerializedName("State")
     * @Serializer\Type("string")
     */
    protected $state;

    /**
     * @var string
     *
     * @Serializer\SerializedName("PostalCode")
     * @Serializer\Type("string")
     */
    protected $postalCode;

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }
}
