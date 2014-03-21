<?php

namespace RentJeeves\LandlordBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Tenant;

/**
 * @Serializer\XmlRoot("Import")
 */
class Import
{
    /**
     * @Serializer\Type("boolean")
     * @Serializer\SerializedName("isSkipped")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $isSkipped;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("number")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $number;

    /**
     * @Serializer\Type("RentJeeves\DataBundle\Entity\Tenant")
     * @Serializer\SerializedName("Tenant")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $tenant;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("csrfToken")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $csrfToken;

    /**
     * @Serializer\SerializedName("moveOut")
     * @Serializer\Groups({"CreditJeeves"})
     * @Serializer\Type("DateTime")
     */
    protected $moveOut = null;

    /**
     * @return mixed
     */
    public function getMoveOut()
    {
        return $this->moveOut;
    }

    /**
     * @param mixed $moveOut
     */
    public function setMoveOut($moveOut)
    {
        $this->moveOut = $moveOut;
    }

    /**
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param integer $number
     */
    public function setNumber($number)
    {
        $this->number = (int)$number;
    }


    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->csrfToken;
    }

    /**
     * @param string $csrfToken
     */
    public function setCsrfToken($csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    /**
     * @return boolean
     */
    public function getIsSkipped()
    {
        return $this->isSkipped;
    }

    /**
     * @param boolean $isSkipped
     */
    public function setIsSkipped($isSkipped)
    {
        $this->isSkipped = $isSkipped;
    }

    /**
     * @return Tenant
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;
    }
}
