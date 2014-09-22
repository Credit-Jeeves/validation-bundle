<?php
namespace RentJeeves\ExperianBundle\NetConnect;

use CreditJeeves\ExperianBundle\Model\NetConnectRequest;
use CreditJeeves\ExperianBundle\NetConnect\CreditProfile as Base;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * It gets credit reports through NetConnect service
 *
 * DI\Service("experian.net_connect.credit_profile") It is defined in services.yml
 */
class CreditProfile extends Base
{
}
