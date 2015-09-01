<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Hip\MandrillBundle\Dispatcher;
use Hip\MandrillBundle\Message;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class Mailer extends BaseMailer implements MailerInterface
{
    /**
     * @var array
     */
    protected $domains = [
        'my.renttrack.com',
        'www.renttrack.com',
        'renttrack.com',
    ];

    /**
     * @var array
     */
    protected $defaultValuesForEmail = [
        'logoName' => 'logo_rj.png',
        'partnerName' => 'RentTrack',
        'partnerAddress' => '4601 Excelsior Blvd Ste 403A, St. Louis Park, MN 55416',
        'loginUrl' => 'my.renttrack.com',
        'isPoweredBy' => false
    ];

    /**
     * @param string $templateName
     * @param array $params
     * @param string $emailTo
     * @param string $culture
     *
     * @return bool
     */
    public function sendBaseLetter($templateName, $params, $emailTo, $culture)
    {
        if (false == $this->isValidEmail($emailTo)) {
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

        $recipientUser = $this->getUserByEmail($emailTo);
        $params = $this->prepareParameters($params, $recipientUser);

        try {
            $htmlContent = $this->manager->renderEmail($template->getName(), $culture, $params);

            $mandrillMessage = $this->createMandrillMessage();
            $mandrillMessage
                ->setFromEmail($htmlContent['fromEmail'])
                ->setFromName($htmlContent['fromName'])
                ->addTo($emailTo)
                ->setSubject($htmlContent['subject']);
            // Add tags and Metadata for Mandrill`s template
            if (null !== $recipientUser) {
                $mandrillMessage
                    ->addTag($recipientUser->getType())
                    ->addMetadata(['user_id' => $recipientUser->getId()]);
            }

            if (false == $mandrillSlug = $template->getEnTranslation()->getMandrillSlug()) {
                $mandrillMessage->setHtml($htmlContent['body']);
                $this->getMandrillMailer()->send($mandrillMessage);
            } else {
                // Add params for Mandrill`s template
                foreach ($params as $key => $param) {
                    $mandrillMessage->addGlobalMergeVar($key, $param);
                }
                $this->getMandrillMailer()->send($mandrillMessage, $mandrillSlug);
            }

            return true;
        } catch (\Twig_Error_Runtime $e) {
            $this->handleException($e);
        } catch (\Mandrill_Error $e) {
            $this->container->get('logger')->alert(sprintf
                (
                    'The MandrillLetter has not been sent : %s',
                    $e->getMessage()
                )
            );
            $this->handleException($e);
        }

        return false;
    }

    /**
     * @param array $params
     * @param User $user
     *
     * @return array
     */
    protected function prepareParameters(array $params, User $user = null)
    {
        // $params is second for higher priority (for test email)
        $params = array_merge($this->defaultValuesForEmail, $params);
        if (null !== $user) {
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

        return $params;
    }

    /**
     * Create new Mandrill Message with needed header
     *
     * @return Message
     */
    protected function createMandrillMessage()
    {
        $mandrillMessage = new Message();
        $mandrillMessage
            ->setTrackClicks(true)
            ->setTrackOpens(true)
            ->setUrlStripQs(true);
        foreach ($this->domains as $domain) {
            $mandrillMessage->addGoogleAnalyticsDomain($domain);
        }

        return $mandrillMessage;
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

        return $this->sendBaseLetter($template, $vars, $user->getEmail(), $user->getCulture());
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

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail($email)
    {
        $constraints = [new Email(), new NotBlank()];
        $errors = $this->container->get('validator')->validateValue(
            $email,
            $constraints
        );

        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @return Dispatcher
     */
    protected function getMandrillMailer()
    {
        return $this->container->get('hip_mandrill.dispatcher');
    }
}
