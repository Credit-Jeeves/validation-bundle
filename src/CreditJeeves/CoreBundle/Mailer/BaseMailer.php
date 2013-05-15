<?php
namespace CreditJeeves\CoreBundle\Mailer;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseMailer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $manager;
    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->manager = $this->container->get('rj_email.email_template_manager');
    }

    protected function sendEmail($user, $sTemplate)
    {
        if (empty($user) || empty($sTemplate)) {
            return false;
        }
        $user = $this->prepareUser($user);
        $isPlain = $this->manager->findTemplateByName($sTemplate.'.text');
        $isHtml = $this->manager->findTemplateByName($sTemplate.'.html');
        if (!empty($isHtml)) {
            $htmlContent = $this->manager->renderEmail(
                $sTemplate.'.html',
                $user['culture'],
                array('user' => $user)
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($htmlContent['subject']);
            $message->setFrom(array($htmlContent['fromEmail'] => $htmlContent['fromName']));
            $message->setTo($user['email']);
            $message->addPart($htmlContent['body'], 'text/html');
            if (!empty($isPlain)) {
                $plainContent = $this->manager->renderEmail(
                    $sTemplate.'.text',
                    $user['culture'],
                    array('user' => $user)
                );
                $message->addPart($plainContent['body'], 'text/plain');
            }
            $this->container->get('mailer')->send($message);
            return true;
        }
        if (!empty($isPlain)) {
            $plainContent = $this->manager->renderEmail(
                $sTemplate.'.text',
                $user['culture'],
                array('user' => $user)
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($plainContent['subject']);
            $message->setFrom(array($plainContent['fromEmail'] => $plainContent['fromName']));
            $message->setTo($user['email']);
            $message->addPart($plainContent['body'], 'text/plain');
            $this->container->get('mailer')->send($message);
            return true;
        }
        return false;
    }

    public function sendTestEmail($sTemplate, $sType = 'text/html')
    {
        if (empty($sTemplate)) {
            return false;
        }
        $isExist = $this->manager->findTemplateByName($sTemplate);
        if (!empty($isExist)) {
            $user = $this->container->get('core.session.admin')->getUser();
            $aEmails = $this->container->getParameter('email_admins');
            $content = $this->manager->renderEmail(
                $sTemplate,
                null,
                array('user' => $this->prepareUser($user))
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($content['subject']);
            $message->setFrom(array($content['fromEmail'] => $content['fromName']));
            $message->setTo($aEmails);
            $message->addPart($content['body'], $sType);
            $this->container->get('mailer')->send($message);
            return true;
        }
        return false;
    }

    public function prepareUser($User)
    {
        $aResult = array();
        $aResult['first_name'] = $User->getFirstName();
        $aResult['middle_initial'] = $User->getMiddleInitial();
        $aResult['last_name'] = $User->getLastName();
        $aResult['full_name'] = $User->getFullName();
        $aResult['email'] = $User->getEmail();
        $score = $User->getScores();
        if (!empty($score)) {
            $aResult['score'] = $score->last()->getScore();
        }
        $aResult['culture'] = $User->getCulture();
        $aResult['ssn'] = $User->displaySsn();
        
        return $aResult;
    }
}
