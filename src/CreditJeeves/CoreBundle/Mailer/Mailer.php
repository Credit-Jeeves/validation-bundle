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
        # \Symfony\Component\DependencyInjection\ContainerInterface::get
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

    public function sendReceipt(Order $order)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        return $this->sendEmail(
            $order->getUser(),
            'receipt',
            array(
                'date' => $order->getCreatedAt()->format($dateShortFormat),
                'amout' => '$9.00', // TODO move to config file and add correct currency formatting
                'number' => $order->getAuthorize()->getTransactionId(),
            )
        );
    }

    public function sendRjCheckEmail($user, $sTemplate = 'rjCheck')
    {
        $url = $this->container->get('router')->generate(
            'tenant_new_check',
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

    public function sendRjLandLordInvite($invite, $sTemplate = 'rjLandLordInvite')
    {
        $isPlain = $this->manager->findTemplateByName($sTemplate.'.text');
        $isHtml = $this->manager->findTemplateByName($sTemplate.'.html');
        $vars = array(
            'nameLandlord'          => $invite->getFirstName(),
            'fullNameTenant'        => $invite->getTenant()->getFullName(),
            'nameTenant'            => $invite->getTenant()->getFirstName(),
            'address'               => $invite->getProperty()->getAddress(),
            'unit'                  => $invite->getUnit(),
        );

        $subject = $invite->getTenant()->getFullName().' wonts to pay her rent using RentTrack';
        $tenant = $invite->getTenant();

        if (empty($isPlain) && empty($isHtml)) {
            $this->handleException(new RuntimeException("Template with key '{$sTemplate}' not found"));
        }

        if (!empty($isHtml)) {
            $htmlContent = $this->manager->renderEmail(
                $sTemplate.'.html',
                $tenant->getCulture(),
                $vars
            );

            $message = \Swift_Message::newInstance();
            $message->setSubject($subject);
            $message->setFrom(array($htmlContent['fromEmail'] => $htmlContent['fromName']));
            $message->setTo($invite->getEmail());
            $message->addPart($htmlContent['body'], 'text/html');
            if (!empty($isPlain)) {
                $plainContent = $this->manager->renderEmail(
                    $sTemplate.'.text',
                    $tenant->getCulture(),
                    $vars
                );
                $message->addPart($plainContent['body'], 'text/plain');
            }
            $this->container->get('mailer')->send($message);
            return true;
        }

        if (!empty($isPlain)) {
            $plainContent = $this->manager->renderEmail(
                $sTemplate.'.text',
                $tenant->getCulture(),
                $vars
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($subject);
            $message->setFrom(array($plainContent['fromEmail'] => $plainContent['fromName']));
            $message->setTo($invite->getEmail());
            $message->addPart($plainContent['body'], 'text/plain');
            $this->container->get('mailer')->send($message);
            return true;
        }
        return false;
    }
}
