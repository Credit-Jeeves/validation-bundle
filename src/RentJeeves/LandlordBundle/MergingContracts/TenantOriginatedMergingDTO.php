<?php

namespace RentJeeves\LandlordBundle\MergingContracts;

use RentJeeves\DataBundle\Entity\Contract;

class TenantOriginatedMergingDTO extends BaseMergingDTO
{
    /**
     * @param Contract $tenantDataContract
     * @param Contract $leaseDataContract
     */
    public function __construct(Contract $tenantDataContract, Contract $leaseDataContract)
    {
        $this->originalContract = $tenantDataContract;
        $this->duplicateContract = $leaseDataContract;
    }

    /**
     * @return Contract
     */
    public function getTenantDataContract()
    {
        return $this->originalContract;
    }

    /**
     * @return Contract
     */
    public function getLeaseDataContract()
    {
        return $this->duplicateContract;
    }
}
