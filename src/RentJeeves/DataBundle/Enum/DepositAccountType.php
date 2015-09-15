<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;
use Doctrine\Common\Collections\Collection;
use RentJeeves\DataBundle\Entity\DepositAccount;

class DepositAccountType extends Enum
{
    /**
     * If you want add new type should add also new translation
     * @see PayAnythingController::payForLisAction
     */
    const APPLICATION_FEE = 'application_fee';
    const SECURITY_DEPOSIT = 'security_deposit';
    const RENT = 'rent';

    /**
     * @param Collection|DepositAccount[] $depositAccounts
     * @return array
     */
    public static function getAvailableChoices(Collection $depositAccounts)
    {
        $result = [];

        foreach ($depositAccounts as $depositAccount) {
            $result[$depositAccount->getType()]= self::title($depositAccount->getType());
        }

        return $result;
    }
}
