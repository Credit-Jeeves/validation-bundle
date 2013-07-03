<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\OrderOperation as Base;

/**
 * OrderOperation
 *
 * @~ORM\Table(name="cj_order_operation")
 * @~ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\OrderOperationRepository")
 *
 * @TODO remove if it would not be in use
 */
class OrderOperation extends Base
{
}
