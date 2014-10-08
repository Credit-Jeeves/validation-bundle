<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileIdentification
{
    /**
     * @Serializer\SerializedName("IDValue")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $balance;

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }


} 