<?php

namespace RentJeeves\LandlordBundle\MergingContracts;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\LandlordBundle\Validator\ContractMergedData;

/**
 * @ContractMergedData(messageUserTypeInvalid="contract.merging.error.email.belongs_another_user")
 */
class ContractMergedDTO extends BaseMergingDTO
{
    /**
     * @param Contract $originalContract
     */
    public function setOriginalContract(Contract $originalContract)
    {
        $this->originalContract = $originalContract;
    }

    /**
     * @param Contract $duplicateContract
     */
    public function setDuplicateContract(Contract $duplicateContract)
    {
        $this->duplicateContract = $duplicateContract;
    }

    /**
     * @return string
     */
    public function getTenantFirstName()
    {
        return $this->tenantFirstName;
    }

    /**
     * @return string
     */
    public function getTenantLastName()
    {
        return $this->tenantLastName;
    }

    /**
     * @return string
     */
    public function getTenantPhone()
    {
        return $this->tenantPhone;
    }

    /**
     * @return string
     */
    public function getTenantEmail()
    {
        return $this->tenantEmail;
    }

    /**
     * @return string
     */
    public function getContractResidentId()
    {
        return $this->contractResidentId;
    }

    /**
     * @return string
     */
    public function getContractLeaseId()
    {
        return $this->contractLeaseId;
    }

    /**
     * @return float
     */
    public function getContractRent()
    {
        return $this->contractRent;
    }

    /**
     * @return integer
     */
    public function getContractDueDate()
    {
        return $this->contractDueDate;
    }

    /**
     * @return \DateTime
     */
    public function getContractStartAt()
    {
        return $this->contractStartAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getContractFinishAt()
    {
        return $this->contractFinishAt;
    }

    /**
     * @return float
     */
    public function getContractIntegratedBalance()
    {
        return $this->contractIntegratedBalance;
    }

    /**
     * @return int|null
     */
    public function getContractPropertyId()
    {
        return $this->contractPropertyId;
    }

    /**
     * @return int|null
     */
    public function getContractUnitId()
    {
        return $this->contractUnitId;
    }

    /**
     * For merged data we take data from form
     *
     * @return null
     */
    protected function getTenantDataContract()
    {
        return null;
    }

    /**
     * For merged data we take data from form
     *
     * @return null
     */
    protected function getLeaseDataContract()
    {
        return null;
    }
}
