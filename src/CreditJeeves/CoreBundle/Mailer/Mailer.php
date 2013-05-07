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
}