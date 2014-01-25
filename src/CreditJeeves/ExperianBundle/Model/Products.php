<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\PreciseIDServer;

/**
 * @Serializer\XmlRoot("Products")
 */
class Products
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\PreciseIDServer")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $preciseIDServer;

    /**
     * @param PreciseIDServer $preciseIDServer
     */
    public function setPreciseIDServer(PreciseIDServer $preciseIDServer)
    {
        $this->preciseIDServer = $preciseIDServer;
    }

    /**
     * @return PreciseIDServer
     */
    public function getPreciseIDServer()
    {
        return $this->preciseIDServer;
    }
}
