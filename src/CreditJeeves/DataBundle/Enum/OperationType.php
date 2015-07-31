<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class OperationType extends Enum
{
    const CHARGE = 'charge';
    const CUSTOM = 'custom';
    const OTHER = 'other';
    const RENT = 'rent';
    const REPORT = 'report';
}
