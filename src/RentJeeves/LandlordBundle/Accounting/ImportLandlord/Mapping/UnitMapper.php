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
     */
    protected function createUnit()
    {
        if (true === $this->isSingleProperty()) {
            $property = $this->getOrCreateProperty();
            $property->addPropertyGroup($this->getGroup());

            return $this->propertyProcess->setupSingleProperty($property, ['doFlush' => false]);
        }

        $newUnit = new Unit();
        $newUnit->setGroup($this->getGroup());
        $newUnit->setHolding($this->getGroup()->getHolding());
        $newUnit->setName($this->get('unitnumber'));
        $newUnit->setProperty($this->getOrCreateProperty());

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
                sprintf('[Mapping] : Address (%s) is not found by PropertyProcess', $this->getAddress())
            );
        }

        if (false === $this->getGroup()->getGroupProperties()->contains($property)) {
            $this->getGroup()->addGroupProperty($property);
        }

        return $property;
    }

    /**
     * @return bool
     */
    protected function isSingleProperty()
    {
        $unitId = $this->get('unitid');

        return empty($unitId);
    }

    /**
     * @return string
     */
    protected function getAddress()
    {
        return sprintf(
            '%s , %s, %s, %s',
            $this->get('streetaddress'),
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
