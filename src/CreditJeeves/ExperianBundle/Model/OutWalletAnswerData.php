<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("OutWalletAnswerData")
 */
class OutWalletAnswerData
{
    /**
     * @Serializer\SerializedName("SessionID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var string
     */
    protected $sessionID;

    /**
     * @Serializer\SerializedName("OutWalletAnswers")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\OutWalletAnswers")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var OutWalletAnswers
     */
    protected $outWalletAnswers;

    /**
     * @return string
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * @param string $sessionID
     *
     * @return $this
     */
    public function setSessionID($sessionID)
    {
        $this->sessionID = $sessionID;

        return $this;
    }

    /**
     * @return OutWalletAnswers
     */
    public function getOutWalletAnswers()
    {
        if (null == $this->outWalletAnswers) {
            $this->outWalletAnswers = new OutWalletAnswers();
        }
        return $this->outWalletAnswers;
    }

    /**
     * @param OutWalletAnswers $outWalletAnswers
     *
     * @return $this
     */
    public function setOutWalletAnswers($outWalletAnswers)
    {
        $this->outWalletAnswers = $outWalletAnswers;

        return $this;
    }
}
