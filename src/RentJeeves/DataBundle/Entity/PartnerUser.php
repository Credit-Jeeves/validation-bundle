<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PartnerUser extends User
{
    protected $type = UserType::PARTNER;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\PartnerUserMapping",
     *     mappedBy="user",
     *     cascade={"all"}
     * )
     */
    protected $partner;

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner ? $this->partner->getPartner() : null;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner($partner)
    {
        if ($this->getId()) {
            $this->updateUserPartner($partner);
        } else {
            $this->addUserPartner($partner);
        }
    }

    /**
     * @param Partner $partner
     */
    protected function addUserPartner(Partner $partner)
    {
        $partnerUser = new PartnerUserMapping();
        $partnerUser->setPartner($partner);
        $partnerUser->setUser($this);
        $this->partner = $partnerUser;
    }

    /**
     * @param Partner $partner
     */
    protected function updateUserPartner(Partner $partner)
    {
        $partnerUser = $this->partner;
        if (!$partnerUser) {
            $partnerUser = new PartnerUserMapping();
            $partnerUser->setUser($this);
        }
        $partnerUser->setPartner($partner);
        $this->partner = $partnerUser;
    }
}
