<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Rj\EmailBundle\Entity\EmailTemplate;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class Mailer extends BaseMailer implements MailerInterface
{
    /**
     * @var array
     */
    protected $defaultValuesForEmail = [
        'logoName' => 'logo_rj.png',
        'partnerName' => 'RentTrack',
        'partnerAddress' => '4601 Excelsior Blvd Ste 403A, St. Louis Park, MN 55416',
        'loginUrl' => 'my.renttrack.com',
        'isPoweredBy' => false,
        'replyToEmail' => 'help@renttrack.com',
    ];

    /**
     * @param string $templateName
     * @param array $params
     * @param User $user
     * @param string $filePath
     * @param bool $noReply
     *
     * @return bool
     */
    public function sendBaseLetter($templateName, $params, User $user, $filePath = null, $noReply = true)
    {
        if (false === $this->isValidEmailSettings($user)) {
            $this->logger->alert(sprintf(
                'Error when sending %s: Notification settings enabled for user (%s %s) without an email address. ' .
                'Either disable notifications or add email to avoid this alert',
                $templateName,
                $user->getFirstName(),
                $user->getLastName()
            ));
        }

        if (false == $this->isValidEmail($user->getEmail())) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('"%s": this value is not a valid email address.', $templateName))
            );

            return false;
        }
        /** \Rj\EmailBundle\Entity\EmailTemplate $template */
        if (null == $template = $this->manager->findTemplateByName($templateName . '.html')) {
            $this->handleException(
                new \InvalidArgumentException(sprintf('Template with name "%s" not found', $templateName))
            );

            return false;
        }


        if (false === MailAuthorizer::isAllowed($templateName, $user)) {
            return false;
        }


        $params = $this->prepareParameters($params, $user);

        try {
            $htmlContent = $this->manager->renderEmail($template->getName(), $user->getCulture(), $params);

            $message = \Swift_Message::newInstance();
            $message->setSubject($htmlContent['subject']);
            $message->setFrom([$htmlContent['fromEmail'] => $params['partnerName']]);
            if (!$noReply) {
                $message->setReplyTo($params['replyToEmail'], $params['partnerName']);
            }

            $message->setTo($user->getEmail());

            if (!empty($filePath)) {
                $message->attach(\Swift_Attachment::fromPath($filePath));
            }

            if (false != $template->getEnTranslation()->getMandrillSlug()) {
                $message = $this->prepareMessageForMandrill($message, $template, $params, $user);
            }

            $message->addPart($htmlContent['body'], 'text/html');

            $this->container->get('mailer')->send($message);

            return true;
        } catch (\Twig_Error $e) {
            $this->handleException($e);
            $this->logger->alert(
                sprintf(
                    'Error when sending email (%s) to user %s : %s',
                    $templateName,
                    $user->getEmail(),
                    $e->getMessage()
                )
            );
        }

        return false;
    }

    /**
     * @param array $params
     * @param string $emailTo
     * @param User $user
     *
     * @return array
     */
    protected function prepareParameters(array $params, User $user)
    {
        // $params is second for higher priority (for test email)
        $params = array_merge($this->defaultValuesForEmail, $params);

        if (false != $partner = $user->getPartner()) {
            if (true === $partner->isPoweredBy()) {
                $params['logoName'] = $partner->getLogoName();
                $params['partnerName'] = $partner->getName();
                $params['partnerAddress'] = $partner->getAddress();
                $params['loginUrl'] = $partner->getLoginUrl();
                $params['isPoweredBy'] = $partner->isPoweredBy();
                $params['replyToEmail'] = $partner->getReplyToEmail();
            }
        }

        $params['emailTo'] = urlencode($user->getEmail());

        return $params;
    }

    /**
     * @param \Swift_Message $message
     * @param EmailTemplate $template
     * @param array $params
     * @param User $user
     *
     * @return \Swift_Message
     */
    protected function prepareMessageForMandrill(
        \Swift_Message $message,
        EmailTemplate $template,
        array $params,
        User $user = null
    ) {
        $headers = $message->getHeaders();
        $headers->addTextHeader('X-MC-Track', 'opens, clicks_htmlonly');
        $headers->addTextHeader('X-MC-GoogleAnalytics', 'my.renttrack.com, www.renttrack.com, renttrack.com');
        $headers->addTextHeader('X-MC-Template', $template->getEnTranslation()->getMandrillSlug());
        $headers->addTextHeader('X-MC-MergeVars', json_encode($params, true));
        if (null !== $user) {
            $headers->addTextHeader('X-MC-Tags', sprintf('%s, %s', $template->getName(), $user->getType()));
            $headers->addTextHeader('X-MC-Metadata', json_encode(['user_id' => $user->getId()]));
        } else {
            $headers->addTextHeader('X-MC-Tags', $template->getName());
        }
        $headers->addTextHeader('X-MC-URLStripQS', 'true');

        return $message;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail($email)
    {
        $errors = $this->container->get('validator')->validateValue(
            $email,
            [new Email(), new NotBlank()]
        );
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }


    /**
     * @param User $user
     * @return bool
     */
    protected function isValidEmailSettings(User $user)
    {
        if (null == $user->getEmail() &&
            (true === $user->getEmailNotification() || true === $user->getOfferNotification())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @param string $sTemplate
     * @param array $vars
     *
     * @return bool
     */
    public function sendEmail($user, $sTemplate, array $vars = array())
    {
        if (empty($user) || empty($sTemplate)) {
            return false;
        }
        $vars['user'] = $this->prepareUser($user);

        return $this->sendBaseLetter($sTemplate, $vars, $user);
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
     * @param  User $user
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

        return $this->sendBaseLetter($template, $vars, $user);
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
