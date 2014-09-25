<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\InitialResults;

/**
 * @Serializer\XmlRoot("OutWalletAnswers")
 */
class OutWalletAnswers
{
    /**
     * @Serializer\SerializedName("OutWalletAnswer1")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var int
     */
    protected $outWalletAnswer1;

    /**
     * @Serializer\SerializedName("OutWalletAnswer2")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var int
     */
    protected $outWalletAnswer2;

    /**
     * @Serializer\SerializedName("OutWalletAnswer3")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var int
     */
    protected $outWalletAnswer3;

    /**
     * @Serializer\SerializedName("OutWalletAnswer4")
     * @Serializer\Type("integer")
     * @Serializer\Groups({"PreciseIDQuestions"})
     * @var int
     */
    protected $outWalletAnswer4;

    /**
     * @return int
     */
    public function getOutWalletAnswer1()
    {
        return $this->outWalletAnswer1;
    }

    /**
     * @param int $outWalletAnswer1
     *
     * @return $this
     */
    public function setOutWalletAnswer1($outWalletAnswer1)
    {
        $this->outWalletAnswer1 = $outWalletAnswer1;

        return $this;
    }

    /**
     * @return int
     */
    public function getOutWalletAnswer2()
    {
        return $this->outWalletAnswer2;
    }

    /**
     * @param int $outWalletAnswer2
     *
     * @return $this
     */
    public function setOutWalletAnswer2($outWalletAnswer2)
    {
        $this->outWalletAnswer2 = $outWalletAnswer2;

        return $this;
    }

    /**
     * @return int
     */
    public function getOutWalletAnswer3()
    {
        return $this->outWalletAnswer3;
    }

    /**
     * @param int $outWalletAnswer3
     *
     * @return $this
     */
    public function setOutWalletAnswer3($outWalletAnswer3)
    {
        $this->outWalletAnswer3 = $outWalletAnswer3;

        return $this;
    }

    /**
     * @return int
     */
    public function getOutWalletAnswer4()
    {
        return $this->outWalletAnswer4;
    }

    /**
     * @param int $outWalletAnswer4
     *
     * @return $this
     */
    public function setOutWalletAnswer4($outWalletAnswer4)
    {
        $this->outWalletAnswer4 = $outWalletAnswer4;

        return $this;
    }
}
