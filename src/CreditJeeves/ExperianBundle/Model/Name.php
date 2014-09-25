<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Name")
 */
class Name
{
    /**
     * @Serializer\SerializedName("Surname")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $surname;

    /**
     * @Serializer\SerializedName("First")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $first;

    /**
     * @Serializer\SerializedName("Middle")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $middle;

    /**
     * @Serializer\SerializedName("Gen")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $gen = null;

    /**
     * @return string
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * @param string $first
     *
     * @return $this
     */
    public function setFirst($first)
    {
        $this->first = $first;
        return $this;
    }

    /**
     * @return string
     */
    public function getGen()
    {
        return $this->gen;
    }

    /**
     * @param string $gen
     *
     * @return $this
     */
    public function setGen($gen)
    {
        $this->gen = $gen;
        return $this;
    }

    /**
     * @return string
     */
    public function getMiddle()
    {
        return $this->middle;
    }

    /**
     * @param string $middle
     *
     * @return $this
     */
    public function setMiddle($middle)
    {
        $this->middle = $middle;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }
}
