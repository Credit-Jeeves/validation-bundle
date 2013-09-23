<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use \RuntimeException;

/**
 * @DI\Service("project.mailer")
 */
class Mailer extends BaseMailer implements MailerInterface
{
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $url = $this->container->get('router')->generate(
            'fos_user_registration_confirm',
            array('token' => $user->getConfirmationToken()),
            true
        );

        return $this->sendEmail(
            $user,
            'confirmation',
            array(
                'confirmationUrl' => $url
            )
        );
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $url = $this->container->get('router')->generate(
            'fos_user_resetting_reset',
            array('token' => $user->getConfirmationToken()),
            true
        );

        return $this->sendEmail(
            $user,
            'resetting',
            array(
                'confirmationUrl' => $url
            )
        );
    }
}
