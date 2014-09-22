<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("OutputType")
 */
class OutputType
{
    /**
     * @Serializer\SerializedName("ARF")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\ARF")
     * @Serializer\Groups({"CreditProfile"})
     * @var ARF
     */
    protected $arf;

    public function __construct()
    {
        $this->getArf();
    }

    /**
     * @return ARF
     */
    public function getArf()
    {
        if (null == $this->arf) {
            $this->arf = new ARF();
        }
        return $this->arf;
    }

    /**
     * @param ARF $arf
     *
     * @return $this
     */
    public function setArf($arf)
    {
        $this->arf = $arf;

        return $this;
    }
}
