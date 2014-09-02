<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Subscriber")
 */
class Subscriber
{
    /**
     * @Serializer\SerializedName("Preamble")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $preamble = 'TBD2';

    /**
     * @Serializer\SerializedName("OpInitials")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $opInitials = 'DE';

    /**
     * @Serializer\SerializedName("SubCode")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $subCode;

    /**
     * @return string
     */
    public function getPreamble()
    {
        return $this->preamble;
    }

    /**
     * @param string $preamble
     *
     * @return $this
     */
    public function setPreamble($preamble)
    {
        $this->preamble = $preamble;

        return $this;
    }

    /**
     * @return string
     */
    public function getOpInitials()
    {
        return $this->opInitials;
    }

    /**
     * @param string $opInitials
     *
     * @return $this
     */
    public function setOpInitials($opInitials)
    {
        $this->opInitials = $opInitials;

        return $this;
    }

    /**
     * @return int
     */
    public function getSubCode()
    {
        return $this->subCode;
    }

    /**
     * @param int $subCode
     *
     * @return $this
     */
    public function setSubCode($subCode)
    {
        $this->subCode = $subCode;

        return $this;
    }
}
