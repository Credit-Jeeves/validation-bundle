<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("ScoreSummary")
 */
class ScoreSummary
{
    /**
     * @Serializer\SerializedName("AcceptReferCode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $acceptReferCode;

    /**
     * @return string
     */
    public function getAcceptReferCode()
    {
        return $this->acceptReferCode;
    }

    /**
     * @param string $acceptReferCode
     *
     * @return $this
     */
    public function setAcceptReferCode($acceptReferCode)
    {
        $this->acceptReferCode = $acceptReferCode;

        return $this;
    }
}
