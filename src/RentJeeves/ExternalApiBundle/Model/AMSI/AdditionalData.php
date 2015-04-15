<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

class AdditionalData
{
    /**
     * @Serializer\SerializedName("eSiteBatchNo")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $eSiteBatchNo;

    /**
     * @Serializer\SerializedName("eSiteBankBookID")
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Groups({"addPaymentResponse"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $eSiteBankBookID;

    /**
     * @return string
     */
    public function getESiteBankBookID()
    {
        return $this->eSiteBankBookID;
    }

    /**
     * @param string $eSiteBankBookID
     */
    public function setESiteBankBookID($eSiteBankBookID)
    {
        $this->eSiteBankBookID = $eSiteBankBookID;
    }

    /**
     * @return string
     */
    public function getESiteBatchNo()
    {
        return $this->eSiteBatchNo;
    }

    /**
     * @param string $eSiteBatchNo
     */
    public function setESiteBatchNo($eSiteBatchNo)
    {
        $this->eSiteBatchNo = $eSiteBatchNo;
    }
}
