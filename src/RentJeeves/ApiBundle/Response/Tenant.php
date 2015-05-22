<?php

namespace RentJeeves\ApiBundle\Response;

use CreditJeeves\DataBundle\Entity\Pidkiq;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqMessageGenerator;
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
     * @var PidKiqMessageGenerator
     *
     * @DI\Inject("pidkiq.message_generator")
     */
    public $pidKiqMessageGenerator;

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
     * @return string format '000-00-0000'
     */
    public function getSsn()
    {
        return $this->entity->getFormattedSsn();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     * @Serializer\SerializedName("verify_status")
     *
     * @return string
     */
    public function getVerificationStatus()
    {
        return $this->entity->getIsVerified();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"TenantDetails"})
     * @Serializer\SerializedName("verify_message")
     *
     * @return string
     */
    public function getVerificationMessage()
    {
        /** @var Pidkiq $pidkiq */
        if ($pidkiq = $this->entity->getPidkiqs()->last()) {
            return $this->pidKiqMessageGenerator->generateMessage($pidkiq->getStatus());
        }

        return '';
    }
}
