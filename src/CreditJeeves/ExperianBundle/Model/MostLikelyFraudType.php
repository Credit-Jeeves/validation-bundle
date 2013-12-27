<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("MostLikelyFraudType")
 */
class MostLikelyFraudType
{
    /**
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\SerializedName("code")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var string
     */
    protected $code;

    /**
     * @Serializer\XmlValue
     * @Serializer\Type("string")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var string
     */
    protected $description;

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
}
