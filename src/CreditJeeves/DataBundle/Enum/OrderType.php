<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Alex Emelyanov <alex.emeluanov.ua@gmail.com>
 */
class OrderType extends Enum
{
    /**
     * @var string
     */
    const HL_CARD = 'heartland_card';

    /**
     * @var string
     */
    const HL_BANK = 'heartland_bank';

    /**
     * @var string
     */
    const CASH = 'cash';
}
