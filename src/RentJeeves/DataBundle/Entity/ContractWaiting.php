<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ContractWaiting as Base;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Contract
 *
 * @ORM\Entity
 * @ORM\Table(name="rj_contract_waiting")
 */
class ContractWaiting extends Base
{
}
