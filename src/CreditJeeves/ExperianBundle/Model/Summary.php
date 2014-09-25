<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("Summary")
 */
class Summary
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("PreciseIDScore")
     * @Serializer\Groups({"CreditJeeves"})
     * @var int
     */
    protected $preciseIDScore;

    /**
     * @Serializer\SerializedName("InitialResults")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\InitialResults")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $initialResults;

    /**
     * @return int
     */
    public function getPreciseIDScore()
    {
        return $this->preciseIDScore;
    }

    /**
     * @param int $preciseIDScore
     *
     * @return $this
     */
    public function setPreciseIDScore($preciseIDScore)
    {
        $this->preciseIDScore = $preciseIDScore;

        return $this;
    }

    /**
     * @return InitialResults
     */
    public function getInitialResults()
    {
        return $this->initialResults;
    }

    /**
     * @param InitialResults $initialResults
     *
     * @return $this
     */
    public function setInitialResults($initialResults)
    {
        $this->initialResults = $initialResults;

        return $this;
    }
}
