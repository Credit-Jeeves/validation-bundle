<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\DataBundle\Enum\Base;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ContractStatus extends Base
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const FINISHED = 'finished';
    const PAID = 'paid';
}
