<?php

namespace RentJeeves\CoreBundle\Services;

use Bazinga\Bundle\GeocoderBundle\Geocoder\LoggableGeocoder as BaseGeoCoder;
use Geocoder\Result\Geocoded;
use Geocoder\Result\ResultInterface;

class GeoCoder extends BaseGeoCoder
{
    /**
     * @var array
     */
    protected $requiredResponseFields = [
        'latitude',
        'longitude',
        'streetNumber',
        'streetName'
    ];

    /**
     * @param string $address
     *
     * @return bool|ResultInterface
     */
    public function getGoogleGeocode($address)
    {
        try {
            $result = $this->using('cache')->geocode($address);
        } catch (\Exception $e) {
            return false;
        }

        if (false === $this->isValidGeoCodedResult($result)) {
            return false;
        }

        return $result;
    }

    /**
     * @param mixed $geoCodedResult
     *
     * @throw \LogicException
     *
     * @return boolean
     */
    protected function isValidGeoCodedResult($geoCodedResult)
    {
        if ($geoCodedResult instanceof Geocoded) {
            foreach ($this->requiredResponseFields as $field) {
                $method = 'get' . ucfirst($field);
                if (false === method_exists($geoCodedResult, $method)) {
                    throw new \LogicException(
                        sprintf(
                            'Incorrect required field \'%s\' for object %s',
                            $field,
                            get_class($geoCodedResult)
                        )
                    );
                }

                $value = $geoCodedResult->$method();

                if (true === empty($value)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
