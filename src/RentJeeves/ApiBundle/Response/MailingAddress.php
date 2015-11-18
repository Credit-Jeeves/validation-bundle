<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use CreditJeeves\DataBundle\Entity\MailingAddress as Entity;

/**
 * @DI\Service("response_resource.address")
 * @UrlResourceMeta(
 *      actionName = "get_address"
 * )
 */
class MailingAddress extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getStreet()
    {
        return $this->entity->getAddress();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getUnit()
    {
        return $this->entity->getUnit();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getCity()
    {
        return $this->entity->getCity();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getState()
    {
        return $this->entity->getArea();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getZip()
    {
        return $this->entity->getZip();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"AddressDetails"})
     * @return string
     */
    public function getIsCurrent()
    {
        return $this->entity->getIsDefault();
    }
}
