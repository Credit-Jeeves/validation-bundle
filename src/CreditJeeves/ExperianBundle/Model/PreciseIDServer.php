<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PreciseIDServer")
 */
class PreciseIDServer
{
    /**
     * @Serializer\SerializedName("XMLVersion")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseID", "CreditJeeves", "PreciseIDQuestions"})
     * @var int
     */
    protected $XMLVersion = 5;

    /**
     * @Serializer\SerializedName("KBAAnswers")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\KBAAnswers")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var KBAAnswers
     */
    protected $kbaAnswers;

    use PreciseIDServerRequest;
    use PreciseIDServerResponse;

    /**
     * @return KBAAnswers
     */
    public function getKbaAnswers()
    {
        if (null == $this->kbaAnswers) {
            $this->kbaAnswers = new KBAAnswers();
        }
        return $this->kbaAnswers;
    }

    /**
     * @param KBAAnswers $kbaAnswers
     *
     * @return $this
     */
    public function setKbaAnswers($kbaAnswers)
    {
        $this->kbaAnswers = $kbaAnswers;

        return $this;
    }
}
