<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class OrderStatus extends Enum
{
    const NEWONE = 'new';
    const COMPLETE = 'complete';
    const ERROR = 'error';
    const CANCELLED = 'cancelled';
}
