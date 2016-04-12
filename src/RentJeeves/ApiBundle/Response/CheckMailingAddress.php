<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\CheckMailingAddress as Entity;

/**
 * @DI\Service("response_resource.check_mailing_address")
 */
class CheckMailingAddress extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     *
     * @return null
     */
    public function getId()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"Base"})
     *
     * @return null
     */
    public function getUrl()
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("payee_name")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getName()
    {
        return $this->entity->getAddressee();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("street_address_1")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getStreetAddress1()
    {
        return $this->entity->getAddress1();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("street_address_2")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getStreetAddress2()
    {
        return (string) $this->entity->getAddress2();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getCity()
    {
        return $this->entity->getCity();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getState()
    {
        return $this->entity->getState();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getZip()
    {
        return $this->entity->getZip();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getLocationId()
    {
        return $this->entity->getExternalLocationId();
    }
}
