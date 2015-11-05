<?php

namespace RentJeeves\CoreBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use Psr\Log\LoggerInterface;
use RentJeeves\ComponentBundle\Service\Google;
use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Unit;

/**
 * Service name "property.manager"
 */
class PropertyManager
{
    const NEW_PROPERTY = "new_property";

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AddressLookupInterface
     */
    protected $addressLookupService;

    /**
     * @var Google
     */
    protected $google;

    /**
     * @var ExceptionCatcher
     */
    protected $exceptionCatcher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $validProperties = [];

    /**
     * @param EntityManagerInterface $em
     * @param Google $google
     * @param AddressLookupInterface $addressLookupService
     * @param ExceptionCatcher $exceptionCatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        Google $google,
        AddressLookupInterface $addressLookupService,
        ExceptionCatcher $exceptionCatcher,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->google = $google;
        $this->addressLookupService = $addressLookupService;
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
     * @throws \RuntimeException
     *
     * @param Property $property
     * @param array $options
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
                throw new \RuntimeException("ERROR: Cannot create a standalone unit for a property without a group");
            } elseif ($groupCount > 1) {
                $groupIds = '';
                foreach ($groups as $group) {
                    $groupIds .= ' ' . $group->getId();
                }
                throw new \RuntimeException(
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

            $propertyAddress = $property->getPropertyAddress();
            $propertyAddress->setIsSingle(true);

            $this->em->persist($unit);

            if ($options['doFlush']) {
                $this->em->flush();
            }

        } elseif ($unitCount === 1) {
            $unit = $property->getUnits()->first();
            if ($unit->getActualName() === Unit::SINGLE_PROPERTY_UNIT_NAME) {
                $this->logger->debug($logPrefix . "Already has single unit -- awesome!");
            } else {
                $msg = $logPrefix . "Has a unit but wrong name";
                $this->logger->error($msg);
                throw new \RuntimeException($msg);
            }
        } else {
            $msg = $logPrefix . "Already has multiple units -- cannot set as single property";
            $this->logger->error($msg);
            throw new \RuntimeException($msg);
        }

        return $unit;
    }

    /**
     * @param Property $property
     */
    public function setupMultiUnitProperty(Property $property)
    {
        $property->getPropertyAddress()->setIsSingle(false);
    }

    /**
     * @param Property $property
     *
     * @return null|Property
     */
    public function checkByMinimalArgs(Property $property)
    {
        $propertyAddress = $property->getPropertyAddress();

        if ($propertyAddress->getIndex() !== null) {
            $params['index'] = $propertyAddress->getIndex();
        } elseif ($propertyAddress->getJb() !== null && $propertyAddress->getKb() !== null) {
            $params['jb'] = $propertyAddress->getJb();
            $params['kb'] = $propertyAddress->getKb();
            $params['number'] = $propertyAddress->getNumber();
        } else {
            throw new \LogicException('Property doesn`t have data about location');
        }

        return $this->getPropertyRepository()->findOneByPropertyAddressFields($params);
    }

    /**
     * @param Property $property
     *
     * @return null|Property
     */
    public function checkByAllArgs(Property $property)
    {
        $propertyAddress = $property->getPropertyAddress();
        $params = [
            'number' => $propertyAddress->getNumber(),
            'city' => $propertyAddress->getCity(),
            'state' => $propertyAddress->getState(),
            'street' => $propertyAddress->getStreet(),
        ];

        return $this->getPropertyRepository()->findOneByPropertyAddressFields($params);
    }

    /**
     * @deprecated pls don`t use it
     *
     * @param Property $property
     * @param bool $saveToGoogle
     *
     * @return null|Property
     */
    public function checkPropertyDuplicate(Property $property, $saveToGoogle = false)
    {
        // verify and standardize address
        if (!$this->isValidProperty($property)) {
            return null;
        }

        foreach (array('checkByMinimalArgs', 'checkByAllArgs') as $method) {
            $propertyInDB = $this->$method($property);
            if ($propertyInDB && $saveToGoogle) {
                $this->saveToGoogle($propertyInDB);

                return $propertyInDB;
            } elseif ($propertyInDB) {
                return $propertyInDB;
            }
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
            throw new \Exception("Can't save invalid property to google");
        }
        try {
            $this->google->savePlace($property);
        } catch (\Exception $e) {
            $this->exceptionCatcher->handleException($e);
        }
    }

    /**
     * @deprecated
     *
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

        $propertyAddress = $property->getPropertyAddress();
        $address = $this->lookupAddress(
            $propertyAddress->getAddress(),
            $propertyAddress->getCity(),
            $propertyAddress->getState(),
            $propertyAddress->getZip()
        );

        if ($address instanceof Address) {
            $propertyAddress->setAddressFields($address);
            $this->validProperties[] = $property;

            return true;
        }

        return false;
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

    /**
     * @param string $number
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     *
     * @return null|Property
     */
    public function getOrCreatePropertyByAddress($number, $street, $city, $state, $zipCode)
    {
        $property = $this->findPropertyByAddressInDb($number, $street, $city, $state, $zipCode);
        if (null !== $property) {
            return $property;
        }

        if (null === $address = $this->lookupAddress($number . ' '. $street, $city, $state, $zipCode)) {
            return null;
        }

        $newProperty = new Property();
        $propertyAddress = new PropertyAddress();

        $propertyAddress->setAddressFields($address);

        $newProperty->setPropertyAddress($propertyAddress);

        return $newProperty;
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     *
     * @return Address|null
     */
    public function lookupAddress($street, $city, $state, $zipCode)
    {
        try {
            return $this->addressLookupService->lookup($street, $city, $state, $zipCode);
        } catch (AddressLookupException $e) {
            return null;
        }
    }

    /**
     * @param string $number
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     *
     * @return Property|null
     */
    public function findPropertyByAddressInDb($number, $street, $city, $state, $zipCode)
    {
        $this->logger->debug(
            sprintf('findPropertyByAddressInDb: %s %s, %s, %s, %', $number, $street, $city, $state, $zipCode)
        );
        $params = [
            'number' => $number,
            'city' => $city,
            'state' => $state,
            'street' => $street,
            'zip' => $zipCode,
        ];
        $params = array_filter($params); // remove empty values
        if (null !== $property = $this->getPropertyRepository()->findOneByPropertyAddressFields($params)) {
            $this->logger->debug(sprintf('Found property(%s) by non-standardized address fields', $property->getId()));

            return $property;
        }
        if (null === $address = $this->lookupAddress($number . ' ' . $street, $city, $state, $zipCode)) {
            $this->logger->debug('Address not found by external address service');

            return null;
        }

        if (null !== $property = $this->getPropertyRepository()->findOneByAddress($address)) {
            $this->logger->debug(sprintf('Found property(%s) by standardized address index!', $property->getId()));

            return $property;
        }

        $params = [
            'number' => $address->getNumber(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'street' => $address->getStreet(),
        ];
        $params = array_filter($params); // remove empty values
        if (null !== $property = $this->getPropertyRepository()->findOneByPropertyAddressFields($params)) {
            $this->logger->debug(sprintf('Found property(%s) by non-standardized address fields', $property->getId()));

            return $property;
        }

        return null;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepository()
    {
        return $this->em->getRepository('RjDataBundle:Property');
    }
}
