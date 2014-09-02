<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("General")
 */
class General
{
    /**
     * @Serializer\SerializedName("KBAResultCode")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $kbaResultCode = 0;

    /**
     * @Serializer\SerializedName("KBAResultCodeDescription")
     * @Serializer\Type("string")
     * @var string
     */
    protected $kbaResultCodeDescription;

    /**
     * @return int
     */
    public function getKbaResultCode()
    {
        return $this->kbaResultCode;
    }

    /**
     * @param int $kbaResultCode
     *
     * @return $this
     */
    public function setKbaResultCode($kbaResultCode)
    {
        $this->kbaResultCode = $kbaResultCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getKbaResultCodeDescription()
    {
        return $this->kbaResultCodeDescription;
    }

    /**
     * @param string $kbaResultCodeDescription
     *
     * @return $this
     */
    public function setKbaResultCodeDescription($kbaResultCodeDescription)
    {
        $this->kbaResultCodeDescription = $kbaResultCodeDescription;

        return $this;
    }
}
