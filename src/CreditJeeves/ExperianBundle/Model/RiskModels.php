<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("RiskModels")
 */
class RiskModels
{
    /**
     * @Serializer\SerializedName("VantageScore3")
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $vantageScore3 = 'Y';

    /**
     * @return string
     */
    public function getVantageScore3()
    {
        return $this->vantageScore3;
    }

    /**
     * @param string $vantageScore3
     *
     * @return $this
     */
    public function setVantageScore3($vantageScore3)
    {
        $this->vantageScore3 = $vantageScore3;

        return $this;
    }
}
