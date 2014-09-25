<?php
namespace CreditJeeves\TestBundle\NetConnect;

use CreditJeeves\ExperianBundle\NetConnect\CreditProfile as Base;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\TestBundle\NetConnect\Traits\CreditProfileTest as CreditProfileTestTrait;

/**
 * DI\Service("experian.net_connect.credit_profile") It is deffined in services.yml
 */
class CreditProfileTest extends Base
{
    use CreditProfileTestTrait;
}
