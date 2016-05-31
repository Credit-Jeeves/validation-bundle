<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup\Model;

use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\DataBundle\Enum\CountryCode;
use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     * @Assert\Choice(callback = "getCountryCodes")
     */
    protected $country;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $state;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $city;

    /**
     * @var string
     */
    protected $district;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $street;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $number;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"SmartyStreetsAddress"})
     */
    protected $zip;

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
     * @var string
     */
    protected $unitName;

    /**
     * @var string
     */
    protected $unitDesignator;

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
     * @return string|null
     */
    public function getIndex()
    {
        if ($this->latitude === null && $this->longitude === null) {
            return null;
        }

        $index = sprintf(
            '%s%s%s%s%s',
            $this->getNumber(),
            $this->getStreet(),
            $this->getCity(),
            $this->getState(),
            $this->getCountry() === AddressLookupInterface::COUNTRY_US ? '' : $this->getCountry()
        );

        return str_replace(' ', '', $index);
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

    /**
     * @return string
     */
    public function getUnitName()
    {
        return $this->unitName;
    }

    /**
     * @param string $unitName
     */
    public function setUnitName($unitName)
    {
        $this->unitName = $unitName;
    }

    /**
     * @return string
     */
    public function getUnitDesignator()
    {
        return $this->unitDesignator;
    }

    /**
     * @param string $unitDesignator
     */
    public function setUnitDesignator($unitDesignator)
    {
        $this->unitDesignator = $unitDesignator;
    }

    /**
     * @return array
     */
    public static function getCountryCodes()
    {
        return CountryCode::all();
    }
}
