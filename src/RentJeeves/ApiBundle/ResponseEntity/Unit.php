<?php

namespace RentJeeves\ApiBundle\ResponseEntity;

use RentJeeves\DataBundle\Entity\Unit as Entity;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Unit
 * @package RentJeeves\ApiBundle\ResponseEntity
 */
class Unit
{
    /**
     * @var Entity
     */
    protected $entity;

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitDetails", "UnitShort"})
     *
     * @return string
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitDetails", "UnitShort"})
     * @Serializer\SerializedName("unit_name")
     *
     * @return string
     */
    public function getUnitName()
    {
        return $this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitDetails", "UnitShort"})
     * @Serializer\SerializedName("property_id")
     *
     * @return string
     */
    public function getPropertyId()
    {
        return $this->entity->getProperty()->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitDetails", "UnitShort"})
     * @Serializer\SerializedName("has_landlord")
     *
     * @return string
     */
    public function hasLandlord()
    {
        if (($group = $this->entity->getGroup() and count($group->getGroupAgents()) > 0)
            or ($holding = $this->entity->getHolding() and count($holding->getHoldingAdmin()) > 0)) {
            return true;
        }

        return false;
    }
}
