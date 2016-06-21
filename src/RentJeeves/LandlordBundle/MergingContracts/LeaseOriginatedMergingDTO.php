<?php

namespace RentJeeves\LandlordBundle\MergingContracts;

use RentJeeves\DataBundle\Entity\Contract;

class LeaseOriginatedMergingDTO extends BaseMergingDTO
{
    /**
     * @param Contract $leaseDataContract
     * @param Contract $tenantDataContract
     */
    public function __construct(Contract $leaseDataContract, Contract $tenantDataContract)
    {
        $this->originalContract = $leaseDataContract;
        $this->duplicateContract = $tenantDataContract;
    }

    /**
     * @return Contract
     */
    public function getLeaseDataContract()
    {
        return $this->originalContract;
    }

    /**
     * @return Contract
     */
    public function getTenantDataContract()
    {
        return $this->duplicateContract;
    }
}
