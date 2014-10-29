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
    protected $partnerService;

    /**
     * @return PartnerService
     */
    public function getPartnerService()
    {
        return $this->partnerService ? $this->partnerService->getPartner() : null;
    }

    /**
     * @param PartnerUser $partnerService
     */
    public function setPartnerService($partnerService)
    {
        if ($this->getId()) {
            $this->updateUserPartner($partnerService);
        } else {
            $this->addUserPartner($partnerService);
        }
    }

    /**
     * @param PartnerService $partnerService
     */
    protected function addUserPartner(PartnerService $partnerService)
    {
        $partnerUser = new PartnerUser();
        $partnerUser->setPartner($partnerService);
        $partnerUser->setUser($this);
        $this->partnerService = $partnerUser;
    }

    /**
     * @param PartnerService $partnerService
     */
    protected function updateUserPartner(PartnerService $partnerService)
    {
        $partnerUser = $this->partnerService;
        if (!$partnerUser) {
            $partnerUser = new PartnerUser();
            $partnerUser->setUser($this);
        }
        $partnerUser->setPartner($partnerService);
        $this->partnerService = $partnerUser;
    }
}
