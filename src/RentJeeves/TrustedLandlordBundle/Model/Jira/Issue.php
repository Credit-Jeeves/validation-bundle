<?php

namespace RentJeeves\TrustedLandlordBundle\Model\Jira;

use JMS\Serializer\Annotation as Serializer;

class Issue
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    protected $key;

    /**
     * @Serializer\Type("RentJeeves\TrustedLandlordBundle\Model\Jira\Fields")
     * @var Fields
     */
    protected $fields;

    /**
     * @return Fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Fields $fields
     */
    public function setFields(Fields $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
