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
    const HEARTLAND_CARD = 'heartland_card';

    /**
     * @var string
     */
    const HEARTLAND_BANK = 'heartland_bank';

    /**
     * @var string
     */
    const CASH = 'cash';
}
