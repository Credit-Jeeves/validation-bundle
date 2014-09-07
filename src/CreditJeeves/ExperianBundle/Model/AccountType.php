<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("AccountType")
 */
class AccountType
{
    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\Groups({"PreciseID", "CreditProfile"})
     * @var string
     */
    protected $type = '3F';

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
