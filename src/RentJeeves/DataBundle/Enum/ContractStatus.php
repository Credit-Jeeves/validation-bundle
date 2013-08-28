<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * Contract statuses
 * Avaliable chains:
 * 1. PENDING -> APPROVED -> CURRENT -> FINISHED
 * 2. INVITE -> APPROVED -> CURRENT -> FINISHED
 * 
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ContractStatus extends \CreditJeeves\CoreBundle\Enum
{
    /**
     * Contract was ordered by Tenant
     * @var string
     */
    const PENDING = 'pending';

    /**
     * Contract was oredred by Landlord
     * @var string
     */
    const INVITE = 'invite';

    /**
     * Contract was approved with another side
     * @var string
     */
    const APPROVED = 'approved';

    /**
     * Approved contract with payments
     * @var string
     */
    const CURRENT = 'current';

    /**
     * Contract was finished
     * @var string
     */
    const FINISHED = 'finished';
}
