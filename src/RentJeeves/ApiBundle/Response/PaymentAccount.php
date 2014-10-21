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
     * @Serializer\Groups({"PaymentAccountDetails"})
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->entity->getName();
    }
}
