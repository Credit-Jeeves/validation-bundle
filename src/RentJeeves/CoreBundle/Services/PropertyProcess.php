<?php

namespace RentJeeves\CoreBundle\Services;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\ComponentBundle\Service\Google;
use RentJeeves\DataBundle\Entity\Property;
use Geocoder\Geocoder;
use Exception;

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
     * @InjectParams({
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "google"           = @Inject("google"),
     *     "geocoder"         = @Inject("bazinga_geocoder.geocoder")
     * })
     */
    public function __construct(
        EntityManager $em,
        Google $google,
        Geocoder $geocoder
    ) {
        $this->geocoder = $geocoder;
        $this->em = $em;
        $this->google = $google;
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
        $this->google->savePlace($property);
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

        $result = $this->geocoder->using('google_maps')->geocode($property->getFullAddress());

        if (empty($result)) {
            return false;
        }

        $property->parseGeocodeResponse($result);
        if (!$this->isSetRequiredFields($property)) {
            return false;
        }

        $this->validProperties[] = $property;

        return true;
    }

    /**
     * @param Property $property
     * @return bool
     */
    protected function isSetRequiredFields(Property $property)
    {
        $fields = array(
            'number',
            'jb',
            'kb',
            'street',
        );

        foreach ($fields as $field) {
            $method = 'get'.ucfirst($field);
            $value = $property->$method();

            if (empty($value)) {
                return false;
            }
        }

        return true;
    }
}
