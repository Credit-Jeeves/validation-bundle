<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("KBAAnswers")
 */
class KBAAnswers
{
    /**
     * @Serializer\SerializedName("OutWalletAnswerData")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\OutWalletAnswerData")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var OutWalletAnswerData
     */
    protected $outWalletAnswerData;

    /**
     * @return OutWalletAnswerData
     */
    public function getOutWalletAnswerData()
    {
        if (null == $this->outWalletAnswerData) {
            $this->outWalletAnswerData = new OutWalletAnswerData();
        }
        return $this->outWalletAnswerData;
    }

    /**
     * @param OutWalletAnswerData $outWalletAnswerData
     *
     * @return $this
     */
    public function setOutWalletAnswerData($outWalletAnswerData)
    {
        $this->outWalletAnswerData = $outWalletAnswerData;

        return $this;
    }
}
