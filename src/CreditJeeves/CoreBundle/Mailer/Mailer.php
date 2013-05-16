<?php
namespace CreditJeeves\CoreBundle\Mailer;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("creditjeeves.mailer")
 */
class Mailer extends BaseMailer
{
    public function sendInviteToApplicant($user, $sTemplate = 'invite')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendWelcomeEmailToApplicant($user, $sTemplate = 'welcome')
    {
        return $this->sendEmail($user, $sTemplate);
    }
}
