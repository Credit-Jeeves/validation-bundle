<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Partner extends User
{
    protected $type = UserType::PARTNER;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerUser",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $partnerApplication;

    /**
     * @return PartnerApplication
     */
    public function getPartnerApplication()
    {
        return $this->partnerApplication ? $this->partnerApplication->getPartner() : null;
    }

    /**
     * @param PartnerUser $partnerApplication
     */
    public function setPartnerApplication($partnerApplication)
    {
        if ($this->getId()) {
            $this->updateUserPartner($partnerApplication);
        } else {
            $this->addUserPartner($partnerApplication);
        }
    }

    /**
     * @param PartnerApplication $partnerApp
     */
    protected function addUserPartner(PartnerApplication $partnerApp)
    {
        $partnerUser = new PartnerUser();
        $partnerUser->setPartner($partnerApp);
        $partnerUser->setUser($this);
        $this->partnerApplication = $partnerUser;
    }

    /**
     * @param PartnerApplication $partnerApp
     */
    protected function updateUserPartner(PartnerApplication $partnerApp)
    {
        $partnerUser = $this->partnerApplication;
        if (!$partnerUser) {
            $partnerUser = new PartnerUser();
            $partnerUser->setUser($this);
        }
        $partnerUser->setPartner($partnerApp);
        $this->partnerApplication = $partnerUser;
    }
}
