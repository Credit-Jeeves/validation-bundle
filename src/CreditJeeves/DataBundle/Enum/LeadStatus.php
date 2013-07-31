<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class LeadStatus extends Enum
{
    const NEWONE = 'new';
    const PREQUAL = 'prequal';
    const ACTIVE = 'active';
    const IDLE = 'idle';
    const READY = 'ready';
    const FINISHED = 'finished';
    const EXPIRED = 'expired';
    const PROCESSED = 'processed';
}
