<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Reasons;

/**
 * @Serializer\XmlRoot("InitialResults")
 */
class InitialResults
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\MostLikelyFraudType")
     * @Serializer\SerializedName("MostLikelyFraudType")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $mostLikelyFraudType;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reasons")
     * @Serializer\SerializedName("Reasons")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reasons;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("InitialDecision")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $InitialDecision;

    /**
     * @param string $InitialDecision
     */
    public function setInitialDecision($InitialDecision)
    {
        $this->InitialDecision = $InitialDecision;
    }

    /**
     * @return string
     */
    public function getInitialDecision()
    {
        return $this->InitialDecision;
    }

    /**
     * @param string $mostLikelyFraudType
     */
    public function setMostLikelyFraudType($mostLikelyFraudType)
    {
        $this->mostLikelyFraudType = $mostLikelyFraudType;
    }

    /**
     * @return string
     */
    public function getMostLikelyFraudType()
    {
        return $this->mostLikelyFraudType;
    }

    /**
     * @param Reasons $reasons
     */
    public function setReasons($reasons)
    {
        $this->reasons = $reasons;
    }

    /**
     * @return Reasons
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
