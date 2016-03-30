<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ProfitStarsBatchStatus;
use RentJeeves\DataBundle\Model\ProfitStarsBatch as Base;

/**
 * @ORM\Table(name="rj_profitstars_batch")
 * @ORM\Entity
 */
class ProfitStarsBatch extends Base
{
    /**
     * Checks if batch status is OPEN
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->status === ProfitStarsBatchStatus::OPEN;
    }

    /**
     * Checks if batch status is CLOSED
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->status === ProfitStarsBatchStatus::CLOSED;
    }
}
