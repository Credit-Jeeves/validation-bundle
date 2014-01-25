<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class OperationType extends Enum
{
    /**
     * @var string
     */
    const REPORT = 'report';

    /**
     * @var string
     */
    const RENT = 'rent';

    /**
     * @var string
     */
    const CHARGE = 'charge';
}
