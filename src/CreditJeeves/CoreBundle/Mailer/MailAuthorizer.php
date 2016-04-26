<?php

namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\DataBundle\Entity\Partner;

class MailAuthorizer
{
    protected static $westsideWhitelist = [
        "rjOrderCancel",
        "rjOrderError",
        "rjOrderReceipt",
        "rjOrderRefunding",
        "rjOrderReissued",
        "rjOrderSending",
        "rjPaymentDue",
        "rjPendingOrder",
        "rjOrderPayDirectComplete",
        "rjTrustedLandlordDenied",
        "rjTrustedLandlordApproved",
        "rjPaymentFlaggedByUntrustedLandlordRule"
    ];

    public static function isAllowed($emailTemplateName, User $user)
    {
        if (self::blockedForUser($user)) {
            return false;
        } elseif (self::blockedForPartner($emailTemplateName, $user)) {
            return false;
        }

        return true;
    }

    protected static function blockedForUser(User $user)
    {
        if (false === $user->getEmailNotification()) {
            return true;
        }

        return false;
    }

    protected static function blockedForPartner($emailTemplateName, User $user)
    {
        /** @var Partner $partner */
        if (false != $partner = $user->getPartner()) {
            switch ($partner->getRequestName()) {
                case 'WESTSIDE':
                    return !in_array($emailTemplateName, self::$westsideWhitelist);
                    break;
            }
        }

        return false;
    }
}
