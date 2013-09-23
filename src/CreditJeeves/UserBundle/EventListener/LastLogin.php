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
use Doctrine\ORM\EntityManager;

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

    /*
    * object EntityManager
    */
    private $em;

    /**
     * @DI\InjectParams({
     *      "em"     = @DI\Inject("doctrine.orm.entity_manager"),
     *      "mailer" = @DI\Inject("mailer")
     * })
     *
     * {@inheritdoc}
     */
    public function __construct(EntityManager $em, $mailer = null)
    {
        $this->mailer = $mailer;
        $this->em = $em;
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

            $user->setLastLogin(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
