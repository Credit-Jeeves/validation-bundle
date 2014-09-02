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
     * @Serializer\SerializedName("PreciseIDServer")
     * @Serializer\Groups({"CreditJeeves", "PreciseID"})
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
        if (null == $this->preciseIDServer) {
            $this->preciseIDServer = new PreciseIDServer();
        }
        return $this->preciseIDServer;
    }
}
