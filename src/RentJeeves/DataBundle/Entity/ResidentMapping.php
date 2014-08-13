<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ResidentMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * ResidentMapping
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\ResidentMappingRepository")
 * @ORM\Table(
 *      name="rj_resident_mapping",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_index_constraint",
 *              columns={
 *                  "tenant_id", "holding_id", "resident_id"
 *              }
 *          )
 *      }
 * )
 *
 */
class ResidentMapping extends Base
{
}
