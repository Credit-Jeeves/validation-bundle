<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Payment as Entity;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

/**
 * @DI\Service("response_resource.payment")
 * @UrlResourceMeta(
 *      actionName = "get_payment"
 * )
 */
class Payment extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getContractUrl()
    {
        return $this
            ->resourceFactory
            ->getResponse($this->entity->getContract());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getPaymentAccountUrl()
    {
        return $this
            ->resourceFactory
            ->getResponse($this->entity->getPaymentAccount());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails", "PaymentShort"})
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     *
     * @return string
     */
    public function getType()
    {
        return $this->entity->getType();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     *
     * @return string
     */
    public function getRent()
    {
        return $this->entity->getAmount();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getOther()
    {
        return number_format($this->entity->getOther(), 2, ".", ""); # no thousands separator
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     * @return integer
     */
    public function getDay()
    {
        return $this->entity->getDueDate();
    }

    /**
     * Get startMonth
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->entity->getStartMonth();
    }

    /**
     * Get startYear
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->entity->getStartYear();
    }

    /**
     * Get endMonth
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return integer
     */
    public function getEndMonth()
    {
        return $this->entity->getEndMonth();
    }

    /**
     * Get endYear
     *
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("string")
     *
     * @return integer
     */
    public function getEndYear()
    {
        return $this->entity->getEndYear();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     *
     * @return DateTime
     */
    public function getPaidFor()
    {
        $paidFor = $this->entity->getPaidFor();

        return $paidFor ? $paidFor->format('Y-m') : "";
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"PaymentDetails"})
     * @Serializer\Type("double")
     *
     * @return double
     */
    public function getFee()
    {
        if ($this->entity->getPaymentAccount()->getType() === PaymentAccountType::CARD) {
            $amount = $this->entity->getAmount();
            $groupFeeCC = $this->entity->getContract()->getGroup()->getGroupSettings()->getFeeCC();

            return round($amount * ($groupFeeCC / 100), 2);
        } else {
            if (true === $this->entity->getContract()->getGroup()->getGroupSettings()->isPassedAch()) {
                return $this->entity->getContract()->getGroup()->getGroupSettings()->getFeeACH();
            }

            return 0;
        }
    }
}
