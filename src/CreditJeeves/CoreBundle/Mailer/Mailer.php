<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use \RuntimeException;
use CreditJeeves\CoreBundle\Mailer\BaseMailer;

/**
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

    public function sendInviteToUser($user, $sTemplate = 'invite')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendWelcomeEmailToUser($user, $sTemplate = 'welcome')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendCheckEmail($user, $sTemplate = 'check')
    {
        $url = $this->container->get('router')->generate(
            'applicant_new_check',
            array('code' => $user->getInviteCode()),
            true
        );
    
        return $this->sendEmail(
            $user,
            $sTemplate,
            array(
                'checkUrl' => $url
            )
        );
    }

    public function sendReportReceipt(Order $order)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        return $this->sendEmail(
            $order->getUser(),
            'receipt',
            array(
                'date' => $order->getCreatedAt()->format($dateShortFormat),
                'amout' => '$9.00', // TODO move to config file and add correct currency formatting
                'number' => $order->getAuthorizes()->last()->getTransactionId(),
            )
        );
    }
}
