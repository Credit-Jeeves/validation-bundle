<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ProfitStarsRegisteredContract as ProfitStarsRegisteredContractModel;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="rj_profitstars_registered_contracts",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="unique_index_constraint", columns={"contract_id", "location_id"})
 *     }
 * )
 */
class ProfitStarsRegisteredContract extends ProfitStarsRegisteredContractModel
{

}
