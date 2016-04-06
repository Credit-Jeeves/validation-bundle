<?php

namespace RentJeeves\CoreBundle\ContractManagement;

use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\Exception\ContractCreatorException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;

class ContractCreator
{
    /**
     * @param Unit        $unit
     * @param Tenant      $tenant
     * @param ContractDTO $contractDTO
     *
     * @throws ContractCreatorException
     *
     * @return Contract
     */
    public function createContract(Unit $unit, Tenant $tenant, ContractDTO $contractDTO)
    {
        throw new ContractCreatorException(); // pls use it

        return new Contract();
    }
}
