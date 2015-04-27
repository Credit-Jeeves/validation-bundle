<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\PropertyMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * PropertyMapping
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="rj_property_mapping",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="unique_index_constraint",
 *              columns={
 *                  "property_id", "holding_id"
 *              }
 *          )
 *      }
 * )
 *
 */
class PropertyMapping extends Base
{
    /**
     * @return string
     */
    public function __toString()
    {

        if ($this->getId()) {
            return sprintf(
                '%s|%s|%s',
                $this->getHolding()->getId(),
                $this->getProperty()->getId(),
                $this->getExternalPropertyId()
            );
        }

        return null;
    }
}
