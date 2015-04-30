<?php

namespace RentJeeves\ApiBundle\Response;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
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

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->entity->getFirstName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->entity->getLastName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->entity->getMiddleInitial();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->entity->getEmail();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->entity->getPhone();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     * @Serializer\Type("DateTime<'Y-m-d'>")
     *
     * @return \DateTime|null
     */
    public function getDateOfBirth()
    {
        return $this->entity->getDateOfBirth();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     *
     * @return bool
     */
    public function getIsVerified()
    {
        return $this->entity->getIsVerified() === UserIsVerified::PASSED;
    }
}
