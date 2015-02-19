<?php

namespace RentJeeves\CoreBundle\Services;

use Geocoder\Result\Geocoded;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\ComponentBundle\Service\Google;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use CreditJeeves\DataBundle\Entity\Group;
use Geocoder\Geocoder;
use Exception;
use RuntimeException;
use Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;

    const NEW_PROPERTY = "new_property";

    /**
     * @InjectParams({
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "google"           = @Inject("google"),
     *     "geocoder"         = @Inject("bazinga_geocoder.geocoder"),
     *     "exceptionCatcher" = @Inject("fp_badaboom.exception_catcher"),
     *     "logger"           = @Inject("logger")
     * })
     */
    public function __construct(
        EntityManager $em,
        Google $google,
        Geocoder $geocoder,
        ExceptionCatcher $exceptionCatcher,
        Logger $logger
    ) {
        $this->geocoder = $geocoder;
        $this->em = $em;
        $this->google = $google;
        $this->exceptionCatcher = $exceptionCatcher;
        $this->logger = $logger;
    }

    /**
     *
     * Sets the property as a single unit property and returns the single unit.
     *
     * Will create the corresponding unit if needed.
     *
     * Options:
     *
     *  'doFlush' : flush new property and unit to DB (if any created). Default: true
     *              If false, you will need to flush yourself.  Setting this to false is useful if you have other
     *              objects that also need flushing.
     *
     * @throws RuntimeException
     * @param Property $property
     * @param array $options optional options.
     *
     * @return Unit
     */
    public function setupSingleProperty(Property $property, array $options = ['doFlush' => true])
    {
        $logPrefix = "Property(" . $this->getPropertyIdentifier($property) . ") ";
        $this->logger->debug($logPrefix . "Attempting to set as single property...");

        $unitCount = $property->getUnits()->count();
        if ($unitCount === 0) {
            $this->logger->debug($logPrefix . "Has no units, so creating one...");
            // create new unit
            $groups = $property->getPropertyGroups();
            $groupCount = $groups->count();
            if ($groupCount < 1) {
                throw new RuntimeException("ERROR: Cannot create a standalone unit for a property without a group");
            } elseif ($groupCount > 1) {
                $groupIds = "";
                foreach ($groups as $group) {
                    $groupIds = $groupIds . " " . $group->getId();
                }
                throw new RuntimeException(
                    "ERROR: Cannot create a standalone unit for a property with multiple groups. Ids: " . $groupIds
                );
            }
            /** @var Group $group */
            $group = $groups->first();

            $unit = new Unit();
            $unit->setProperty($property);
            $unit->setGroup($group);
            $unit->setHolding($group->getHolding());
            $unit->setName(UNIT::SINGLE_PROPERTY_UNIT_NAME);
            $property->addUnit($unit);

            $property->setIsSingle(true);

            $this->em->persist($property);
            $this->em->persist($unit);

            if ($options['doFlush']) {
                $this->em->flush($property);
                $this->em->flush($unit);
            }

        } elseif ($unitCount === 1) {
            $unit = $property->getUnits()->first();
            if ($unit->getActualName() === Unit::SINGLE_PROPERTY_UNIT_NAME) {
                $this->logger->debug($logPrefix . "Already has single unit -- awesome!");
            } else {
                $msg = $logPrefix . "Has a unit but wrong name";
                $this->logger->error($msg);
                throw RuntimeException($msg);
            }
        } else {
            $msg = $logPrefix . "Already has multiple units -- cannot set as single property";
            $this->logger->error($msg);
            throw RuntimeException($msg);
        }

        return $unit;
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

    private function getPropertyIdentifier(Property $property)
    {
        $identifier = self::NEW_PROPERTY;

        if ($id = $property->getId()) {
            $identifier = $id;
        } elseif ($addr = trim($property->getFullAddress())) {
            $identifier = $addr;
        }

        return $identifier;
    }
}
