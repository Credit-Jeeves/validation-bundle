<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Reason1;
use CreditJeeves\ExperianBundle\Model\Reason2;
use CreditJeeves\ExperianBundle\Model\Reason3;
use CreditJeeves\ExperianBundle\Model\Reason4;
use CreditJeeves\ExperianBundle\Model\Reason5;

/**
 * @Serializer\XmlRoot("Reasons")
 */
class Reasons
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reason1")
     * @Serializer\SerializedName("Reason1")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reason1;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reason2")
     * @Serializer\SerializedName("Reason2")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reason2;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reason3")
     * @Serializer\SerializedName("Reason3")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reason3;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reason4")
     * @Serializer\SerializedName("Reason4")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reason4;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Reason5")
     * @Serializer\SerializedName("Reason5")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $reason5;

    /**
     * @param Reason1 $reason1
     */
    public function setReason1($reason1)
    {
        $this->reason1 = $reason1;
    }

    /**
     * @return Reason1
     */
    public function getReason1()
    {
        return $this->reason1;
    }

    /**
     * @param Reason2 $reason2
     */
    public function setReason2($reason2)
    {
        $this->reason2 = $reason2;
    }

    /**
     * @return Reason2
     */
    public function getReason2()
    {
        return $this->reason2;
    }

    /**
     * @param Reason3 $reason3
     */
    public function setReason3($reason3)
    {
        $this->reason3 = $reason3;
    }

    /**
     * @return Reason3
     */
    public function getReason3()
    {
        return $this->reason3;
    }

    /**
     * @param Reason4 $reason4
     */
    public function setReason4($reason4)
    {
        $this->reason4 = $reason4;
    }

    /**
     * @return Reason4
     */
    public function getReason4()
    {
        return $this->reason4;
    }

    /**
     * @param Reason5 $reason5
     */
    public function setReason5($reason5)
    {
        $this->reason5 = $reason5;
    }

    /**
     * @return Reason5
     */
    public function getReason5()
    {
        return $this->reason5;
    }
}
