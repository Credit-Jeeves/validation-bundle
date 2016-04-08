<?php

namespace RentJeeves\TrustedLandlordBundle\Model\Jira;

use JMS\Serializer\Annotation as Serializer;

class Type
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
