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
     * @Serializer\Groups({"CreditJeeves", "PreciseID", "PreciseIDQuestions"})
     * @var PreciseIDServer
     */
    protected $preciseIDServer;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\CreditProfile")
     * @Serializer\SerializedName("CreditProfile")
     * @Serializer\Groups({"CreditProfile"})
     * @var CreditProfile
     */
    protected $creditProfile;

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

    /**
     * @param PreciseIDServer $preciseIDServer
     *
     * @return $this
     */
    public function setPreciseIDServer(PreciseIDServer $preciseIDServer)
    {
        $this->preciseIDServer = $preciseIDServer;

        return $this;
    }

    /**
     * @return CreditProfile
     */
    public function getCreditProfile()
    {
        if (null == $this->creditProfile) {
            $this->creditProfile = new CreditProfile();
        }
        return $this->creditProfile;
    }

    /**
     * @param CreditProfile $creditProfile
     *
     * @return $this
     */
    public function setCreditProfile(CreditProfile $creditProfile)
    {
        $this->creditProfile = $creditProfile;

        return $this;
    }
}
