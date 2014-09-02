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
     */
    protected $preciseIDScore;

    /**
     * @Serializer\SerializedName("InitialResults")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\InitialResults")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $initialResults;

    /**
     * @return mixed
     */
    public function getPreciseIDScore()
    {
        return $this->preciseIDScore;
    }

    /**
     * @param mixed $preciseIDScore
     *
     * @return $this
     */
    public function setPreciseIDScore($preciseIDScore)
    {
        $this->preciseIDScore = $preciseIDScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInitialResults()
    {
        return $this->initialResults;
    }

    /**
     * @param mixed $initialResults
     *
     * @return $this
     */
    public function setInitialResults($initialResults)
    {
        $this->initialResults = $initialResults;

        return $this;
    }
}
