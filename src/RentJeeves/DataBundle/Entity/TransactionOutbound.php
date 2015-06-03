<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\TransactionOutbound as BaseTransactionOutbound;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_transaction_outbound")
 */
class TransactionOutbound extends BaseTransactionOutbound
{
}
