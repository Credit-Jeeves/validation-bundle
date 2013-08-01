<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ContractStatus extends \CreditJeeves\CoreBundle\Enum
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const FINISHED = 'finished';
    const PAID = 'paid';
    const ACTIVE = 'active';
}
