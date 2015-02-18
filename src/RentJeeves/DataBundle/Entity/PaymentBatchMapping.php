<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\PaymentBatchMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository")
 * @ORM\Table(
 *      name="rj_payment_batch_mapping"
 * )
 *
 */
class PaymentBatchMapping extends Base
{
}
