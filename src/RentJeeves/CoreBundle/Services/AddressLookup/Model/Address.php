<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $country;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $state;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $city;

    /**
     * @var string
     */
    protected $district;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $street;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $number;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress", "GoogleAddress"})
     */
    protected $zip;

    /**
     * @var string using for GeoCoder
     *
     * @Assert\NotBlank(groups={"GoogleAddress"})
     */
    protected $jb;

    /**
     * @var string using for GeoCoder
     *
     * @Assert\NotBlank(groups={"GoogleAddress"})
     */
    protected $kb;

    /**
     * @var string using for SS
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $latitude;

    /**
     * @var string using for SS
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $longitude;

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
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
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getJb()
    {
        return $this->jb;
    }

    /**
     * @param string $jb
     */
    public function setJb($jb)
    {
        $this->jb = $jb;
    }

    /**
     * @return string
     */
    public function getKb()
    {
        return $this->kb;
    }

    /**
     * @param string $kb
     */
    public function setKb($kb)
    {
        $this->kb = $kb;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param string $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->getNumber(). ' ' . $this->getStreet();
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        /** @TODO: add logic */
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        return sprintf(
            '%s %s, %s%s, %s %s',
            $this->getNumber(),
            $this->getStreet(),
            $this->getDistrict() ? $this->getDistrict() . ', ' : '',
            $this->getCity(),
            $this->getState(),
            $this->getZip()
        );
    }
}
