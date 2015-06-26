<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\DataBundle\Entity\Contract as Entity;

/**
 * @DI\Service("response_resource.contract")
 * @UrlResourceMeta(
 *      actionName = "get_contract"
 * )
 */
class Contract extends ResponseResource
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
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
     * @Serializer\Groups({"ContractDetails"})
     * @return string
     */
    public function getStatus()
    {
        return $this->entity->getStatus();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return string
     */
    public function getRent()
    {
        return number_format($this->entity->getRent(), 2, '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return string
     */
    public function getLeaseStart()
    {
        return ($this->entity->getStartAt()) ? $this->entity->getStartAt()->format('Y-m-d') : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return string
     */
    public function getLeaseEnd()
    {
        return ($this->entity->getFinishAt()) ? $this->entity->getFinishAt()->format('Y-m-d') : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return int|string
     */
    public function getDueDate()
    {
        return $this->entity->getDueDate() ? $this->entity->getDueDate() : '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return string
     */
    public function getExperianReporting()
    {
        return $this->entity->getReportToExperian() ? ReportingType::ENABLED : ReportingType::DISABLED;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @return string|null
     */
    public function getBalance()
    {
        if ($this->entity->getGroup()) {
            return $this->entity->getGroup()->getGroupSettings()->getIsIntegrated() ?
                number_format($this->entity->getIntegratedBalance(), 2, '.', '') : null;
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("mailing_address")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Group")
     * @return string
     */
    public function getMailingAddress()
    {
        return $this->entity->getGroup();
    }
}
