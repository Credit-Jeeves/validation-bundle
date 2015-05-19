<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use CreditJeeves\DataBundle\Entity\Pidkiq as Entity;

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
     * @Serializer\Accessor(getter="getMessage",setter="setMessage")
     * @Serializer\Groups({"IdentityVerificationDetails"})
     *
     * @var string
     */
    protected $message;

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
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
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
        return $this->entity->getCreatedAt()->modify('+10 minutes')->format('U');
    }
}
