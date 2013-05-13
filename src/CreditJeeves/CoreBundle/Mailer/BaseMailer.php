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
        if (!empty($isHtml)) {
            $htmlContent = $this->manager->renderEmail(
                $sTemplate.'.html',
                $user->getCulture(),
                array('user' => $user)
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
                $user->getCulture(),
                array('user' => $user)
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
}
