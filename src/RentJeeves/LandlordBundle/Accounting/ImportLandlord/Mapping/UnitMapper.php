<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\DuplicatedUnitException;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;

/**
 * @method Unit map(array $data, Group $group = null)
 */
class UnitMapper extends AbstractMapper
{
    /**
     * @var PropertyManager
     */
    protected $propertyManager;

    /**
     * @param PropertyManager $propertyManager
     */
    public function __construct(PropertyManager $propertyManager)
    {
        $this->propertyManager = $propertyManager;
    }

    /**
     * @throws DuplicatedUnitException
     *
     * @return Unit
     */
    protected function mapObject()
    {
        if (null === $group = $this->getGroup()) {
            throw new \LogicException('Please send the group as 2nd parameter for function map');
        }

        $holding = $group->getHolding();
        if (null !== $holding->getId() &&
            null !== $this->getUnitRepository()->findOneByHoldingAndExternalId($holding, $this->get('unitid'))
        ) {
            throw new DuplicatedUnitException(
                sprintf(
                    '[Mapping] : Unit with externalId#%s and Holding#%d already exists',
                    $this->get('unitid'),
                    $holding->getId()
                )
            );
        }

        return $this->createUnit();
    }

    /**
     * @return Unit
     *
     * @throws MappingException
     */
    protected function createUnit()
    {
        $unitNumber = $this->get('unitnumber');
        if (true === empty($unitNumber)) {
            $newUnit = $this->createSinglePropertyUnit();
        } else {
            $property = $this->getOrCreateProperty();
            if ($property->getId() !== null && $property->getPropertyAddress()->isSingle()) {
                throw new MappingException(
                    sprintf(
                        '[Mapping] : We can`t create new Unit for single Property#%d',
                        $property->getId()
                    )
                );
            }

            $newUnit = new Unit();
            $newUnit->setGroup($this->getGroup());
            $newUnit->setHolding($this->getGroup()->getHolding());
            $newUnit->setName($this->get('unitnumber'));
            $newUnit->setProperty($property);
        }

        $newUnit->setUnitMapping($this->createUnitMapping($newUnit));

        return $newUnit;
    }

    /**
     * @return Unit
     *
     * @throws MappingException
     */
    protected function createSinglePropertyUnit()
    {
        $property = $this->getOrCreateProperty();
        if ($property->getId() !== null) {
            throw new MappingException(
                sprintf(
                    '[Mapping] : We can`t create one more SinglePropertyUnit for existing Property#%d',
                    $property->getId()
                )
            );
        }
        $property->addPropertyGroup($this->getGroup()); // for correct work propertyProcess

        try {
            $newUnit = $this->propertyManager->setupSingleProperty($property, ['doFlush' => false]);
        } catch (\RuntimeException $e) {
            throw new MappingException(sprintf('[Mapping] : %s', $e->getMessage()));
        }

        return $newUnit;
    }

    /**
     * @param Unit $unit
     *
     * @return UnitMapping
     */
    protected function createUnitMapping(Unit $unit)
    {
        $newUnitMapping = new UnitMapping();
        $newUnitMapping->setExternalUnitId($this->get('unitid'));
        $newUnitMapping->setUnit($unit);

        return $newUnitMapping;
    }

    /**
     * @throws MappingException
     *
     * @return Property
     */
    protected function getOrCreateProperty()
    {
        $property = $this->propertyManager->getOrCreatePropertyByAddress(
            '',
            $this->get('streetaddress'),
            $this->get('city_name'),
            $this->get('state_name'),
            $this->get('zipcode')
        );

        if ($property === null) {
            throw new MappingException(
                sprintf(
                    '[Mapping] : Address (%s , %s, %s, %s) is not found by PropertyManager',
                    $this->get('streetaddress'),
                    $this->get('city_name'),
                    $this->get('state_name'),
                    $this->get('zipcode')
                )
            );
        }

        if (false === $this->getGroup()->getGroupProperties()->contains($property)) {
            $this->getGroup()->addGroupProperty($property);
        }

        return $property;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepository()
    {
        return $this->em->getRepository('RjDataBundle:Unit');
    }
}
