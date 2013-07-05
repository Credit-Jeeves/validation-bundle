<?php
namespace CreditJeeves\UserBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @DI\Service("user.event_listener.last_login")
 *
 * @DI\Tag("kernel.event_subscriber")
 */
class LastLogin implements EventSubscriberInterface
{
    private $mailer;

    /**
     * @DI\InjectParams({
     *     "mailer" = @DI\Inject("creditjeeves.mailer")
     * })
     *
     * {@inheritdoc}
     */
    public function __construct($mailer = null)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return array(
                SecurityEvents::INTERACTIVE_LOGIN => array('onSecurityInteractiveLogin', 255),
        );
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof UserInterface) {
            $lastLogin = $user->getLastLogin();
            if (empty($lastLogin) & 'applicant' == $user->getType()) {
                $this->mailer->sendWelcomeEmailToApplicant($user);
            }
        }
    }
}
