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
        $isPlain = $this->manager->findTemplateByName($sTemplate.'.text');
        $isHtml = $this->manager->findTemplateByName($sTemplate.'.html');
        if (!empty($isPlain)) {
            $textContent = $this->manager->renderEmail($sTemplate.'.text', $user->getCulture(), array('name' => $user->getFirstName()));
            $message = \Swift_Message::newInstance();
            $message->setSubject($textContent['subject']);
            $message->setFrom(array($textContent['fromEmail'] => $textContent['fromName']));
            $message->setTo($user->getEmail());
            $message->setBody($textContent['body'], 'text/plain');
            if (!empty($isHtml)) {
                $htmlContent = $this->manager->renderEmail($sTemplate.'.html', $user->getCulture(), array('name' => $user->getFirstName()));
                $message->addPart($htmlContent['body'], 'text/html');
            }
            $this->container->get('mailer')->send($message);
            return true;
        }
        if (!empty($isHtml)) {
            $htmlContent = $this->manager->renderEmail($sTemplate.'.html', $user->getCulture(), array('name' => $user->getFirstName()));
            $message->setSubject($htmlContent['subject']);
            $message->setFrom(array($htmlContent['fromEmail'] => $textContent['fromName']));
            $message->setTo($user->getEmail());
            $message->setBody($htmlContent['body'], 'text/html');
            $this->container->get('mailer')->send($message);
            return true;
        }
        return false;
    }
}