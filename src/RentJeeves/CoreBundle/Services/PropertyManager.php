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
        $propertyIdentifier = $property->getId() ?: ($property->getFullAddress() ?: self::NEW_PROPERTY);
        $logPrefix = sprintf('Property(%s)', $propertyIdentifier);
        $this->logger->debug(sprintf('%s Attempting to set as single property.', $logPrefix));

        $unitCount = $property->getUnits()->count();
        if ($unitCount === 0) {
            $this->logger->debug(sprintf('%s Has no units, so creating one.', $logPrefix));
            // create new unit
            $groups = $property->getPropertyGroups();
            $groupCount = $groups->count();
            if ($groupCount < 1) {
                throw new \RuntimeException(
                    sprintf('%s ERROR: Cannot create a standalone unit for a property without a group', $logPrefix)
                );
            } elseif ($groupCount > 1) {
                $groupIds = '';
                foreach ($groups as $group) {
                    $groupIds .= ' ' . $group->getId();
                }
                throw new \RuntimeException(
                    sprintf(
                        '%s ERROR: Cannot create a standalone unit for a property with multiple groups. Ids: %s',
                        $logPrefix,
                        $groupIds
                    )
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
                $this->logger->debug(sprintf('%s Already has single unit -- awesome!', $logPrefix));
            } else {
                $this->logger->error($msg = sprintf('%s Has a unit but wrong name', $logPrefix));
                throw new \RuntimeException($msg);
            }
        } else {
            $msg = sprintf('%s Already has multiple units -- cannot set as single property', $logPrefix);
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
     */
    public function saveToGoogle(Property $property)
    {
        try {
            $this->google->savePlace($property);
        } catch (\Exception $e) {
            $this->exceptionCatcher->handleException($e);
        }
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

        if (null === $address = $this->lookupAddress($number . ' ' . $street, $city, $state, $zipCode)) {
            return null;
        }

        return $this->createPropertyByAddress($address);
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
            sprintf('findPropertyByAddressInDb: %s %s, %s, %s, %s', $number, $street, $city, $state, $zipCode)
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

        $invalidAddressIndex = self::generateInvalidAddressIndex($number, $street, $city, $state);
        if (null !== $property = $this->findByInvalidIndex($invalidAddressIndex)) {
            $this->logger->debug(sprintf('Found manually added property(%s)', $property->getId()));

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
     * Here is where we look for those invalid property addresses that had to be added manually
     *
     * @param $index
     *
     * @return null|Property
     */
    protected function findByInvalidIndex($index)
    {
        $this->logger->debug(
            sprintf('findByInvalidIndex: %s', $index)
        );
        $params = [
            'index' => $index
        ];
        if (null !== $property = $this->getPropertyRepository()->findOneByPropertyAddressFields($params)) {
            $this->logger->debug(sprintf('Found property(%s) by invalid address index', $property->getId()));

            return $property;
        }

        return null;
    }

    /**
     * Sometimes we have to add properties manually -- because they are new construction or
     * not deliverable by the USPS.
     *
     * When adding a property address manually, be sure to use this method to generate the index
     * Or it may not be found.  You have been warned!
     *
     * Example:
     *
     *      Takes this Address: "3217 S. Babcock St Melbourne FL"
     *      And returns this index: "3217sbabcockstmelbourneflinvalidaddress"
     *
     * @param string $number
     * @param string $street
     * @param string $city
     * @param string $state
     *
     * @return string
     */
    protected function generateInvalidAddressIndex($number, $street, $city, $state)
    {
        $index = sprintf('%s%s%s%sinvalidaddress', $number, $street, $city, $state);
        $index = str_replace(' ', '', $index);
        $index = str_replace('.', '', $index);
        $index = strtolower($index);

        return $index;
    }

    /**
     * @param Address $address
     *
     * @return Property
     */
    protected function createPropertyByAddress(Address $address)
    {
        $newProperty = new Property();
        $propertyAddress = new PropertyAddress();

        $propertyAddress->setAddressFields($address);

        $newProperty->setPropertyAddress($propertyAddress);

        return $newProperty;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\PropertyRepository
     */
    protected function getPropertyRepository()
    {
        return $this->em->getRepository('RjDataBundle:Property');
    }
}
