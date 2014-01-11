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
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\InitialResults")
     * @Serializer\SerializedName("InitialResults")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $initialResults;

    /**
     * @param InitialResults $initialResults
     */
    public function setInitialResult(InitialResults $initialResults)
    {
        $this->initialResults = $initialResults;
    }

    /**
     * @return InitialResults
     */
    public function getInitialResult()
    {
        return $this->initialResults;
    }
}
