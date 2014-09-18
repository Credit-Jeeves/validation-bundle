<?php

namespace RentJeeves\ApiBundle\ResponseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Property as Entity;

/**
 * Class Property
 * @package RentJeeves\ApiBundle\ResponseEntity
 */
class Property
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
     * @Serializer\Groups({"PropertyDetails"})
     *
     * @return string
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     * @Serializer\SerializedName("address")
     *
     * @return string
     */
    public function getDetailsAddress()
    {
        return [
            'country'   => $this->entity->getCountry(),
            'area'      => $this->entity->getArea(),
            'city'      => $this->entity->getCity(),
            'district'  => $this->entity->getDistrict(),
            'street'    => $this->entity->getStreet(),
            'number'    => $this->entity->getNumber(),
            'zip'       => $this->entity->getZip(),
        ];
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     * @Serializer\SerializedName("full_address")
     *
     * @return string
     */
    public function getFullAddress()
    {
        return $this->entity->getFullAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     *
     * @return string
     */
    public function getLan()
    {
        return $this->entity->getKb();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     *
     * @return string
     */
    public function getLat()
    {
        return $this->entity->getJb();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     * @Serializer\SerializedName("is_single")
     *
     * @return string
     */
    public function getIsSingle()
    {
        return ($this->entity->getIsSingle()) ?  true : false;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PropertyDetails"})
     * @Serializer\SerializedName("unit_count")
     *
     * @return int
     */
    public function getUnitCount()
    {
        if ($this->getIsSingle()) {
            return 1;
        }

        return $this->entity->getUnits()->count();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Units"})
     *
     * @return array
     */
    public function getUnits()
    {
        return new ResponseCollection($this->entity->getUnits()->toArray());
    }
}
