<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\AciCollectPayUserProfile as Base;

/**
 * @ORM\Table(name="rj_aci_collect_pay_user_profile")
 * @ORM\Entity
 */
class AciCollectPayUserProfile extends Base
{
    /**
     * @param string $divisionId
     * @return bool
     */
    public function hasBillingAccountForDivisionId($divisionId)
    {
        if (null !== $this->getBillingAccountForDivisionId($divisionId)) {
            return true;
        }

        return false;
    }

    /**
     * @param $divisionId
     * @return AciCollectPayProfileBilling|null
     */
    public function getBillingAccountForDivisionId($divisionId)
    {
        foreach ($this->getAciCollectPayProfileBillings() as $billingAccount) {
            if ($billingAccount->getDivisionId() === $divisionId) {
                return $billingAccount;
            }
        }

        return null;
    }
}
