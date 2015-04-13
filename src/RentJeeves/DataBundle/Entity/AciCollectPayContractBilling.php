<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciCollectPayContractBilling as Base;

/**
 * @ORM\Table(name="rj_aci_collect_pay_contract_billing")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\AciCollectPayContractBillingRepository")
 */
class AciCollectPayContractBilling extends Base
{
}
