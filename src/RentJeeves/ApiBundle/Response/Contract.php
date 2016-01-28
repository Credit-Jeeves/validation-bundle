<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\DataBundle\Entity\Contract as Entity;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

/**
 * @DI\Service("response_resource.contract")
 * @UrlResourceMeta(
 *      actionName = "get_contract"
 * )
 */
class Contract extends ResponseResource
{
    const DELIVERY_METHOD_ELECTRONIC = 'electronic';
    const DELIVERY_METHOD_CHECK = 'check';

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails", "ContractShort"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getUnitUrl()
    {
        return $this
            ->resourceFactory
            ->getResponse($this->entity->getUnit());
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails", "ContractShort"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails", "ContractShort"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getRent()
    {
        return number_format($this->entity->getRent(), 2, '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getLeaseStart()
    {
        return ($this->entity->getStartAt()) ? $this->entity->getStartAt()->format('Y-m-d') : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getLeaseEnd()
    {
        return ($this->entity->getFinishAt()) ? $this->entity->getFinishAt()->format('Y-m-d') : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails", "ContractShort"})
     * @Serializer\Type("string")
     * @return int|string
     */
    public function getDueDate()
    {
        return $this->entity->getDueDate() ? $this->entity->getDueDate() : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getExperianReporting()
    {
        return $this->entity->getReportToExperian() ? ReportingType::ENABLED : ReportingType::DISABLED;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails", "ContractShort"})
     * @Serializer\Type("string")
     * @return string|null
     */
    public function getBalance()
    {
        if ($this->entity->getGroup()) {
            return $this->entity->getGroupSettings()->getIsIntegrated() ?
                number_format($this->entity->getIntegratedBalance(), 2, '.', '') : null;
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("fee_cc")
     * @Serializer\Type("float")
     * @return float
     */
    public function getFeeCC()
    {
        if ($this->entity->getGroup()) {
            return (float) $this->entity->getGroupSettings()->getFeeCC();
        }

        throw new \LogicException('Contract should have a group.');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("fee_ach")
     * @Serializer\Type("float")
     * @return float
     */
    public function getFeeACH()
    {
        if ($this->entity->getGroup()) {
            return $this->entity->getGroupSettings()->isPassedAch() ?
                (float) $this->entity->getGroupSettings()->getFeeACH() :
                0.0;
        }

        throw new \LogicException('Contract should have a group.');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("delivery_method")
     * @Serializer\Type("string")
     * @return string
     */
    public function getDeliveryMethod()
    {
        $orderAlgorithm = $this->entity->getGroup()->getOrderAlgorithm();

        return $orderAlgorithm === OrderAlgorithmType::SUBMERCHANT ?
            self::DELIVERY_METHOD_ELECTRONIC : self::DELIVERY_METHOD_CHECK;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("mailing_address")
     * @Serializer\Type("RentJeeves\ApiBundle\Response\Group")
     * @return Group
     */
    public function getMailingAddress()
    {
        return $this
            ->resourceFactory
            ->getResponse($this->entity->getGroup());
    }
}
