<?php
namespace CreditJeeves\DataBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class OrderStatus extends Base
{
    const NEWONE = 'new';
    const COMPLETE = 'complete';
    const ERROR = 'error';
    const CANCELLED = 'cancelled';
}
