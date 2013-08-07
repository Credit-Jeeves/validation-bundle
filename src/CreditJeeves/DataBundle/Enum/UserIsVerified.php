<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class UserIsVerified extends Enum
{
    const NONE = 'none';
    const FAILED = 'failed';
    const LOCKED = 'locked';
    const PASSED = 'passed';
}
