<?php
namespace RentJeeves\ExperianBundle\NetConnect;

use CreditJeeves\ExperianBundle\NetConnect\PreciseID as Base;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * PreciseID (Pidkiq) is used for verifying user's identity.
 *
 * DI\Service("experian.net_connect.precise_id") It is defined in services.yml
 */
class PreciseID extends Base
{
}
