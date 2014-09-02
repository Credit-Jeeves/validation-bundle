<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("KBAScore")
 */
class KBAScore
{
    /**
     * @Serializer\SerializedName("General")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\General")
     * @var General
     */
    protected $general;

    /**
     * @return General
     */
    public function getGeneral()
    {
        if (null == $this->general) {
            $this->general = new General();
        }
        return $this->general;
    }

    /**
     * @param General $general
     *
     * @return $this
     */
    public function setGeneral($general)
    {
        $this->general = $general;

        return $this;
    }
}
