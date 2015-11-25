<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\DataBundle\Entity\Unit as Entity;

/**
 * @DI\Service("response_resource.unit")
 * @UrlResourceMeta(
 *      actionName = "get_unit"
 * )
 */
class Unit extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitShort", "UnitDetails"})
     *
     * @return string
     */
    public function getName()
    {
        return $this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UnitDetails"})
     * @Serializer\SerializedName("address")
     * @Serializer\Type("array")
     * @return string
     */
    public function getAddress()
    {
        $propertyAddress = $this->entity->getProperty()->getPropertyAddress();

        return [
            'street' => sprintf('%s %s', $propertyAddress->getNumber(), $propertyAddress->getStreet()),
            'city' => $propertyAddress->getCity(),
            'state' => $propertyAddress->getState(),
            'zip' => $propertyAddress->getZip()
        ];
    }
}
