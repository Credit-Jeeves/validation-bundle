<?php
namespace RentJeeves\TestBundle\NetConnect;

use CreditJeeves\ExperianBundle\NetConnect\PreciseID;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\TestBundle\NetConnect\Traits\PreciseIDTest as PreciseIDTestTrait;

/**
 * DI\Service("experian.net_connect.precise_id") It is deffined in services.yml
 */
class PreciseIDTest extends PreciseID
{
    use PreciseIDTestTrait;
}
