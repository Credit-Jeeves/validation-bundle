<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class Address
{
    /**
     * @Serializer\SerializedName("Street")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $street;

    /**
     * @Serializer\SerializedName("City")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $city;

    /**
     * @Serializer\SerializedName("State")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $state;

    /**
     * @Serializer\SerializedName("Zip")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var int
     */
    protected $zip;

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
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
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
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
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return int
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param int $zip
     *
     * @return $this
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }
}
