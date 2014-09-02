<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Error;

/**
 * @Serializer\XmlRoot("PreciseIDServer")
 */
class PreciseIDServer
{
    /**
     * @Serializer\SerializedName("XMLVersion")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseID", "CreditJeeves"})
     *
     * @var int
     */
    protected $XMLVersion = 5;

    use PreciseIDServerRequest;
    use PreciseIDServerResponse;
}
