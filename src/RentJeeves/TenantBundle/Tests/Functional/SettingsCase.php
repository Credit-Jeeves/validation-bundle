<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use CreditJeeves\UserBundle\Tests\Traits\SettingsCaseTrait;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class SettingsCase extends BaseTestCase
{
    use SettingsCaseTrait;

    protected $userEmail = 'tenant11@example.com';
    protected $accountLink = 'common.account';
}
