<?php
namespace CreditJeeves\DataBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class UserIsVerified extends Base
{
    const NONE = 'none';
    const FAILED = 'failed';
    const LOCKED = 'locked';
    const PASSED = 'passed';
}
