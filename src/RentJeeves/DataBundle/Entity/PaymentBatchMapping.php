<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\PaymentBatchMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository")
 * @ORM\Table(
 *      name="rj_payment_batch_mapping",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_index_constraint",
 *              columns={
 *                  "payment_batch_id", "payment_processor", "accounting_package_type"
 *              }
 *          )
 *      }
 * )
 *
 */
class PaymentBatchMapping extends Base
{
}
