<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Operation as Base;

/**
 * Operation
 *
 * @ORM\Table(name="cj_operation")
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OperationRepository")
 */
class Operation extends Base
{
}
