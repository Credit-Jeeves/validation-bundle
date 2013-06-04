<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("creditjeeves.mailer")
 */
class Mailer extends BaseMailer implements MailerInterface
{
    public function sendInviteToApplicant($user, $sTemplate = 'invite')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendWelcomeEmailToApplicant($user, $sTemplate = 'welcome')
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
//var_dump($url);
//        var_dump($this->container->get('router')->getContext());die("Ok\n");


        return $this->sendEmail(
            $user,
            'resetting',
            array(
                'confirmationUrl' => $url
            )
        );
    }

    public function sendReceipt(Order $order)
    {
        return $this->sendEmail(
            $order->getUser(),
            'receipt',
            array(
                'date' => $order->getCreatedAt()->format('M j, Y'),
                'amout' => '$9.00', // TODO move to config file and add correct currency formatting
                'number' => $order->getAuthorize()->getTransactionId(),
            )
        );
    }
}
