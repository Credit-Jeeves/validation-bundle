<?php

namespace RentJeeves\LandlordBundle\Services;

use RentJeeves\DataBundle\Entity\Contract;
use JMS\Serializer\Annotation as Serializer;

class ContractMergedDTO
{
    /**
     * @var Contract
     */
    protected $leaseDataContract;

    /**
     * @var Contract
     */
    protected $tenantDataContract;

    /**
     * @param Contract $tenantDataContract
     * @param Contract $leaseDataContract
     */
    public function __construct(Contract $tenantDataContract, Contract $leaseDataContract)
    {
        $this->tenantDataContract = $tenantDataContract;
        $this->leaseDataContract = $leaseDataContract;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("leaseDataContractId")
     *
     * @return int
     */
    public function getLeaseDataContractId()
    {
        return $this->leaseDataContract->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("tenantDataContractId")
     *
     * @return int
     */
    public function getTenantDataContractId()
    {
        return $this->tenantDataContract->getId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("firstName")
     *
     * @return string
     */
    public function getTenantFirstName()
    {
        return $this->tenantDataContract->getTenant()->getFirstName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("lastName")
     *
     * @return string
     */
    public function getTenantLastName()
    {
        return $this->tenantDataContract->getTenant()->getLastName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("phone")
     *
     * @return string
     */
    public function getTenantPhone()
    {
        return $this->tenantDataContract->getTenant()->getPhone();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("email")
     *
     * @return string
     */
    public function getTenantEmail()
    {
        return $this->tenantDataContract->getTenant()->getEmail();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("residentId")
     *
     * @return string
     */
    public function getTenantResidentId()
    {
        if ($this->tenantDataContract->getGroup()->isAllowedEditResidentId() &&
            $residentMapping = $this->tenantDataContract->getTenant()->getResidentForHolding(
                $this->tenantDataContract->getHolding()
            )
        ) {
            return $residentMapping->getResidentId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("leaseId")
     *
     * @return string
     */
    public function getContractLeaseId()
    {
        if ($this->leaseDataContract->getGroup()->isAllowedEditLeaseId()) {
            return $this->leaseDataContract->getId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("amount")
     *
     * @return string
     */
    public function getContractRent()
    {
        return $this->leaseDataContract->getRent();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("dueDate")
     *
     * @return int
     */
    public function getContractDueDate()
    {
        return $this->leaseDataContract->getDueDate();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("start")
     *
     * @return int
     */
    public function getContractStartAt()
    {
        return $this->leaseDataContract->getStartAt();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("finish")
     * @Serializer\
     *
     * @return int
     */
    public function getContractFinishAt()
    {
        return $this->leaseDataContract->getFinishAt();
    }

    public function getContractIntegratedBalance()
    {
        if ($this->leaseDataContract->getGroupSettings()->getIsIntegrated()) {
            return $this->leaseDataContract->getIntegratedBalance();
        }

        return 0;
    }
}
