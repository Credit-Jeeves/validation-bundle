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
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Summary")
     * @Serializer\SerializedName("Summary")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $summary;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Error")
     * @Serializer\SerializedName("Error")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $error;

    /**
     * @param Summary $summary
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return Summary
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param Error $error
     */
    public function setError(Error $error)
    {
        $this->error = $error;
    }

    /**
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }
}
