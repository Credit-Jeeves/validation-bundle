<?php

namespace RentJeeves\CoreBundle\Services;

use Geocoder\Result\Geocoded;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\ComponentBundle\Service\Google;
use RentJeeves\DataBundle\Entity\Property;
use Geocoder\Geocoder;
use Exception;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("property.process")
 */
class PropertyProcess
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Geocoder
     */
    protected $geocoder;

    /**
     * @var Google
     */
    protected $google;

    /**
     * @var array
     */
    protected $validProperties = array();

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @InjectParams({
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "google"           = @Inject("google"),
     *     "geocoder"         = @Inject("bazinga_geocoder.geocoder"),
     *     "exceptionCatcher" = @Inject( "fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(
        EntityManager $em,
        Google $google,
        Geocoder $geocoder,
        ExceptionCatcher $exceptionCatcher
    ) {
        $this->geocoder = $geocoder;
        $this->em = $em;
        $this->google = $google;
        $this->exceptionCatcher = $exceptionCatcher;
    }

    /**
     * @param array $params
     *
     * @return null|object
     */
    public function getPropertyFromDB(array $params)
    {
        foreach ($params as $key => $param) {
            if (empty($param)) {
                unset($params[$key]);
            }
        }

        if (count($params) < 3) {
            return null;
        }

        return $this->em->getRepository(
            'RjDataBundle:Property'
        )->findOneBy($params);
    }

    /**
     * @param Property $property
     * @return null|Property
     */
    public function checkByMinimalArgs(Property $property)
    {
        $params = array(
            'jb'        => $property->getJb(),
            'kb'        => $property->getKb(),
            'number'    => $property->getNumber(),
        );

        return $this->getPropertyFromDB($params);
    }

    /**
     * @param Property $property
     * @return null|Property
     */
    public function checkByAllArgs(Property $property)
    {
        $params = array(
            'number'    => $property->getNumber(),
            'city'      => $property->getCity(),
            'district'  => $property->getDistrict(),
            'area'      => $property->getArea(),
            'street'    => $property->getStreet(),
            'country'   => $property->getCountry(),
        );

        return $this->getPropertyFromDB($params);
    }

    /**
     * @param Property $property
     * @param bool $saveToGoogle
     *
     * @return null|Property
     */
    public function checkPropertyDuplicate(
        Property $property,
        $saveToGoogle = false
    ) {
        foreach (array('checkByMinimalArgs', 'checkByAllArgs') as $method) {
            $propertyInDB = $this->$method($property);
            if ($propertyInDB && $saveToGoogle) {
                $this->saveToGoogle($propertyInDB);
                return $propertyInDB;
            } elseif ($propertyInDB) {
                return $propertyInDB;
            }
        }

        if (!$this->isValidProperty($property)) {
            return null;
        }

        if ($saveToGoogle) {
            $this->saveToGoogle($property);
        }

        return $property;
    }

    /**
     * @param Property $property
     * @throws \Exception
     */
    public function saveToGoogle(Property $property)
    {
        if (!$this->isValidProperty($property)) {
            throw new Exception("Can't save invalid property to google");
        }
        try {
            $this->google->savePlace($property);
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
        }
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isValidProperty(Property $property)
    {
        foreach ($this->validProperties as $propertyValid) {
            if ($property === $propertyValid) {
                return true;
            }
        }

        if ($response = $this->getGoogleGeocode($property->getFullAddress())) {
            $this->mapGeocodeResponseToProperty($response, $property);
            $this->validProperties[] = $property;

            return true;
        }

        return false;
    }

    /**
     * @param $address
     * @return bool|Geocoded
     */
    public function getGoogleGeocode($address)
    {
        try {
            $result = $this->geocoder->using('cache')->geocode($address);
        } catch (Exception $e) {
            $this->exceptionCatcher->handleException($e);
            return false;
        }
        if (empty($result)) {
            return false;
        }

        if (!$this->isSetRequiredFields($result)) {
            return false;
        }

        return $result;
    }

    /**
     * @param Geocoded $response
     * @param Property $property
     * @return Property
     */
    public function mapGeocodeResponseToProperty(Geocoded $response, Property $property)
    {
        $property->setLatitude($response->getLatitude());
        $property->setLongitude($response->getLongitude());
        $property->setZip($response->getZipcode());
        $property->setArea($response->getRegionCode());
        $property->setCountry($response->getCountryCode());
        $property->setCity($response->getCity());
        if (!$property->getCity()) {
            $property->setCity($response->getCityDistrict());
        }
        $property->setNumber($response->getStreetNumber());
        $property->setStreet($response->getStreetName());
        $property->setDistrict($response->getCityDistrict());

        return $property;
    }

    /**
     * @param Geocoded $googleResult
     * @return bool
     */
    protected function isSetRequiredFields(Geocoded $googleResult)
    {
        $fields = array(
            'latitude',
            'longitude',
            'streetNumber',
            'streetName'
        );

        foreach ($fields as $field) {
            $method = 'get'.ucfirst($field);
            $value = $googleResult->$method();

            if (empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $address
     * @return null|Property
     */
    public function getPropertyByAddress($address)
    {
        $property = new Property();

        if ($googleResult = $this->getGoogleGeocode($address)) {
            $property = $this->mapGeocodeResponseToProperty($googleResult, $property);

            if ($propertyDB = $this->checkByMinimalArgs($property) or
                $propertyDB = $this->checkByAllArgs($property)) {
                /** Property */
                return $propertyDB;
            }
            /** Empty Property */
            return $property;
        }
        /** Error Address not found */
        return null;
    }
}
