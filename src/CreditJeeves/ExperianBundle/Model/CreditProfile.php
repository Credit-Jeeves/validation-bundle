<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Error;

/**
 * @Serializer\XmlRoot("CreditProfile")
 */
class CreditProfile
{
    use CreditProfileRequest;
//    use PreciseIDServerResponse;
}
