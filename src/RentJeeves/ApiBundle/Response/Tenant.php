<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\DataBundle\Entity\Tenant as Entity;

/**
 * @DI\Service("response_resource.tenant")
 * @UrlResourceMeta(
 *      actionName = "get_user"
 * )
 */

class Tenant extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"UserDetails"})
     *
     * @return string
     */
    public function getType()
    {
        return $this->entity->getType();
    }
}
