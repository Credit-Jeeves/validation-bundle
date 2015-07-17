<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\DuplicatedUnitException;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;

/**
 * @method Unit map(array $data, Group $group = null)
 */
class UnitMapper extends AbstractMapper
{
    /**
     * @var PropertyProcess
     */
    protected $propertyProcess;

    /**
     * @param PropertyProcess $propertyProcess
     */
    public function __construct(PropertyProcess $propertyProcess)
    {
        $this->propertyProcess = $propertyProcess;
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
            null !== $this->getUnitRepository()->findOneByHoldingAndExternalId($holding, $this->get('UnitID'))
        ) {
            throw new DuplicatedUnitException(
                sprintf(
                    '[Mapping] : Unit with externalId#%s and Holding#%d already exists',
                    $this->get('UnitID'),
                    $holding->getId()
                )
            );
        }

        return $this->createUnit();
    }

    /**
     * @return Unit
     */
    protected function createUnit()
    {
        $newUnit = new Unit();
        $newUnit->setGroup($group = $this->getGroup());
        $newUnit->setHolding($group->getHolding());
        $newUnit->setName($this->get('UnitNumber') ?: $this->get('UnitID'));
        $newUnit->setProperty($this->getOrCreateProperty());

        $this->em->persist($newUnit);

        return $newUnit;
    }

    /**
     * @throws MappingException
     *
     * @return Property
     */
    protected function getOrCreateProperty()
    {
        $property = $this->propertyProcess->getPropertyByAddress($this->getAddress());
        if ($property === null) {
            throw new MappingException(
                sprintf('[Mapping] : Address (%s) is not found by geocoder', $this->getAddress())
            );
        }

        if (false === $property->getPropertyGroups()->contains($this->getGroup())) {
            $property->addPropertyGroup($this->getGroup());
        }

        if ($property->getId() === null) {
            $this->em->persist($property);
        }

        return $property;
    }

    /**
     * @return string
     */
    protected function getAddress()
    {
        return sprintf(
            '%s , %s, %s, %s',
            $this->get('StreetAddress'),
            $this->get('city_name'),
            $this->get('state_name'),
            $this->get('zipcode')
        );
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepository()
    {
        return $this->em->getRepository('RjDataBundle:Unit');
    }
}
