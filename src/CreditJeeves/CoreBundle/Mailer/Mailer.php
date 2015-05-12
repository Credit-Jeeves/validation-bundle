<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;

class Mailer extends BaseMailer implements MailerInterface
{
    /**
     * @var array
     */
    protected $defaultValuesForEmail = [
        'logoName' => 'logo_rj.png',
        'partnerName' => 'RentTrack',
        'partnerAddress' => '13911 Ridgedale Drive, Suite 401C, Minnetonka, MN 55305',
        'loginUrl' => 'my.renttrack.com',
        'isPoweredBy' => false
    ];

    /**
     * @param User   $user
     * @param string $sTemplate
     * @param array  $vars
     *
     * @return bool
     */
    public function sendEmail($user, $sTemplate, array $vars = array())
    {
        if (empty($user) || empty($sTemplate)) {
            return false;
        }
        $vars['user'] = $this->prepareUser($user);

        return $this->sendBaseLetter($sTemplate, $vars, $user->getEmail(), $user->getCulture());
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

        $kernel = $this->container->get('kernel');
        if ($kernel->getName() === 'rj') {
            return $this->sendEmail(
                $user,
                'rj_resetting',
                array(
                    'confirmationUrl' => $url
                )
            );
        }

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

    /**
     * @param  User  $user
     * @return array
     */
    public function prepareUser($user)
    {
        $aResult = array();
        $aResult['first_name'] = $user->getFirstName();
        $aResult['middle_initial'] = $user->getMiddleInitial();
        $aResult['last_name'] = $user->getLastName();
        $aResult['full_name'] = $user->getFullName();
        $aResult['email'] = $user->getEmail();
        $score = $user->getScores()->last();
        if (!empty($score)) {
            $aResult['score'] = $score->getScore();
        }
        $aResult['culture'] = $user->getCulture();
        $aResult['ssn'] = $user->displaySsn();
        $aResult['code'] = $user->getInviteCode();

        return $aResult;
    }

    public function sendTargetApplicant(Lead $lead, $template = 'target')
    {
        $user = $lead->getUser();
        $vars = array(
            'loginLink' => $this->container->get('router')->generate(
                'fos_user_security_login',
                array(),
                true
            ),
            'targetScore' => $lead->getTargetScore(),
        );

        return $this->sendBaseLetter($template, $vars, $user->getEmail(), $user->getCulture());
    }

    /**
     * @param string $templateName
     * @param array  $params
     * @param string $emailTo
     * @param string $culture
     *
     * @return bool
     */
    public function sendBaseLetter($templateName, $params, $emailTo, $culture)
    {
        /** \Rj\EmailBundle\Entity\EmailTemplate $template */
        if (null == $template = $this->manager->findTemplateByName($templateName . '.html')) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('Template with name "%s" not found', $templateName))
            );

            return false;
        }
        try {
            // $params is second for higher priority (for test email)
            $params = array_merge($this->defaultValuesForEmail, $params);
            if (null !== $user = $this->getUserByEmail($emailTo)) {
                if (false != $partner = $user->getPartner()) {
                    if (true === $partner->isPoweredBy()) {
                        $params['logoName'] = $partner->getLogoName();
                        $params['partnerName'] = $partner->getName();
                        $params['partnerAddress'] = $partner->getAddress();
                        $params['loginUrl'] = $partner->getLoginUrl();
                        $params['isPoweredBy'] = $partner->isPoweredBy();
                    }
                }
            }

            $htmlContent = $this->manager->renderEmail($template->getName(), $culture, $params);

            $message = \Swift_Message::newInstance();
            $message->setSubject($htmlContent['subject']);
            $message->setFrom([$htmlContent['fromEmail'] => $htmlContent['fromName']]);
            $message->setTo($emailTo);
            $message->addPart($htmlContent['body'], 'text/html');

            $this->container->get('mailer')->send($message);

            return true;
        } catch (\Twig_Error_Runtime $e) {
            $this->handleException($e);
        }

        return false;
    }

    /**
     * @param string $email
     *
     * @return User|null
     */
    protected function getUserByEmail($email)
    {
        return $this->container->get('doctrine')->getManager()
            ->getRepository('DataBundle:User')
            ->findOneBy(['email' => $email]);
    }
}
