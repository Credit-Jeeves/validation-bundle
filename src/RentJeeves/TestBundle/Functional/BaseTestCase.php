<?php
namespace RentJeeves\TestBundle\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase as Base;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
abstract class BaseTestCase extends Base
{

    const APP = 'AppRj';

    protected $envPath = '/rj_test.php/';
}
