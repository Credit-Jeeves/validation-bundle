<?php

namespace RentJeeves\CoreBundle\Helpers;

use RentJeeves\DataBundle\Enum\CountryCode;

class CountryNameStandardizer
{
    /**
     * @var array
     */
    protected static $iso2 = [
        CountryCode::US => 'US',
        CountryCode::CA => 'CA',
    ];

    /**
     * @var array
     */
    protected static $iso3 = [
        CountryCode::US => 'USA',
        CountryCode::CA => 'CAN',
    ];

    /**
     * @var array
     */
    protected static $fullNames = [
        CountryCode::US => 'United States of America',
        CountryCode::CA => 'Canada',
    ];

    /**
     * Standardize input country to RentTrack format country or return input country
     *
     * @param string $country
     *
     * @return string
     */
    public static function standardize($country)
    {
        if (false !== $key = array_search($country, self::$iso3)) {
            return $key;
        }

        if (false !== $key = array_search($country, self::$iso2)) {
            return $key;
        }

        if (false !== $key = array_search($country, self::$fullNames)) {
            return $key;
        }

        return $country;
    }

    /**
     * Standardize input RentTrack format country to ISO2 format or return null
     *
     * @param string $country
     *
     * @return string|null
     */
    public static function standardizeToISO2($country)
    {
        return true === isset(self::$iso2[$country]) ? self::$iso2[$country] : null;
    }

    /**
     * Standardize input RentTrack format country to ISO3 format or return null
     *
     * @param string $country
     *
     * @return string|null
     */
    public static function standardizeToISO3($country)
    {
        return true === isset(self::$iso3[$country]) ? self::$iso3[$country] : null;
    }
}
