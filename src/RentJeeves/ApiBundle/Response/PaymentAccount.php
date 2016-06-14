<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\DataBundle\Entity\PaymentAccount as Entity;

/**
 * @DI\Service("response_resource.payment_account")
 * @UrlResourceMeta(
 *      actionName = "get_payment_account"
 * )
 */
class PaymentAccount extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentAccountShort", "PaymentAccountDetails"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentAccountDetails", "PaymentAccountShort"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getType()
    {
        return $this->entity->getType();
    }

    /**
     * Format: "YYYY-mm"
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentAccountDetails"})
     * @Serializer\Type("string")
     *
     * @return string|null
     */
    public function getExpiration()
    {
        return $this->entity->getCcExpiration() ? $this->entity->getCcExpiration()->format('Y-m') : null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentAccountDetails"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getBillingAddressUrl()
    {
        if ($this->entity->getAddress()) {
            return $this
                ->resourceFactory
                ->getResponse($this->entity->getAddress());
        }

        return '';
    }
}
