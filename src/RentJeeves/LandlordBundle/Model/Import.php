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
