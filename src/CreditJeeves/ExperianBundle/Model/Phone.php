<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Phone")
 */
class Phone
{
    /**
     * @Serializer\SerializedName("Number")
     * @Serializer\Groups({"PreciseID"})
     * @var int
     */
    protected $number;

    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $type = null;

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
