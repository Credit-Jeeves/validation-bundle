<?php

namespace RentJeeves\ApiBundle\ResponseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Landlord as Entity;

class Landlord
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
     * @Serializer\Groups({"LandlordDetails"})
     *
     * @return string
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"LandlordDetails"})
     * @Serializer\SerializedName("first_name")
     * @return string
     */
    public function getFirstName()
    {
        return $this->entity->getFirstName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"LandlordDetails"})
     * @Serializer\SerializedName("last_name")
     * @return string
     */
    public function getLastName()
    {
        return $this->entity->getLastName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"LandlordDetails"})
     * @return string
     */
    public function getPhone()
    {
        return $this->entity->getPhone();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"LandlordDetails"})
     * @return string
     */
    public function getEmail()
    {
        return $this->entity->getEmail();
    }
}
