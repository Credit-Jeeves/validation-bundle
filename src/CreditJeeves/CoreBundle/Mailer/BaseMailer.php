<?php
namespace CreditJeeves\CoreBundle\Mailer;

use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Exception;
use \RuntimeException;

abstract class BaseMailer
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ExceptionCatcher
     */
    private $catcher;

    protected $manager;

    /**
     * @todo remove container and pass all required services
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "catcher" = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     *
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container, ExceptionCatcher $catcher = null)
    {
        $this->catcher = $catcher;
        $this->container = $container;
        $this->manager = $this->container->get('rj_email.email_template_manager');
    }

    protected function handleException(Exception $e)
    {
        if ($this->catcher) {
            $this->catcher->handleException($e);
        } else {
            throw $e;
        }
    }

    public function sendEmail($user, $sTemplate, array $vars = array())
    {
        if (empty($user) || empty($sTemplate)) {
            return false;
        }
        $vars['user'] = $this->prepareUser($user);

        $isPlain = $this->manager->findTemplateByName($sTemplate.'.text');
        $isHtml = $this->manager->findTemplateByName($sTemplate.'.html');
        if (empty($isPlain) && empty($isHtml)) {
            $this->handleException(new RuntimeException("Template with key '{$sTemplate}' not found"));
        }

        if (!empty($isHtml)) {
            $htmlContent = $this->manager->renderEmail(
                $sTemplate.'.html',
                $user->getCulture(),
                $vars
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($htmlContent['subject']);
            $message->setFrom(array($htmlContent['fromEmail'] => $htmlContent['fromName']));
            $message->setTo($user->getEmail());
            $message->addPart($htmlContent['body'], 'text/html');
            if (!empty($isPlain)) {
                $plainContent = $this->manager->renderEmail(
                    $sTemplate.'.text',
                    $user->getCulture(),
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
                $user->getCulture(),
                $vars
            );
            $message = \Swift_Message::newInstance();
            $message->setSubject($plainContent['subject']);
            $message->setFrom(array($plainContent['fromEmail'] => $plainContent['fromName']));
            $message->setTo($user->getEmail());
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
        $score = $User->getScores()->last();
        if (!empty($score)) {
            $aResult['score'] = $score->getScore();
        }
        $aResult['culture'] = $User->getCulture();
        $aResult['ssn'] = $User->displaySsn();
        
        return $aResult;
    }
}
