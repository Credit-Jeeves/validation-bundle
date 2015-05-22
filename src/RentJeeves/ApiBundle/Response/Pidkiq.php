<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use CreditJeeves\DataBundle\Entity\Pidkiq as Entity;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqMessageGenerator;

/**
 * @DI\Service("response_resource.identity_verification")
 * @UrlResourceMeta(
 *      actionName = "get_identity_verification"
 * )
 */
class Pidkiq extends ResponseResource
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
     * @var string
     * @DI\Inject("%pidkiq.lifetime.minutes%")
     */
    public $lifetime;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->pidKiqMessageGenerator->generateMessage(
            $this->entity->getStatus()
        );
    }

    /**
     * @Serializer\Type("array")
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return array
     */
    public function getQuestions()
    {
        return $this->entity->getQuestions();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->entity->getCreatedAt()->modify('+' . (int) $this->lifetime . ' minutes')->format('U');
    }
}
