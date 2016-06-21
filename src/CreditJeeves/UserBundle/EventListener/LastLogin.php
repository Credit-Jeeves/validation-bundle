<?php
namespace CreditJeeves\UserBundle\EventListener;

use CreditJeeves\CoreBundle\Mailer\Mailer;
use CreditJeeves\DataBundle\Enum\UserType;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
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
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @DI\InjectParams({
     *      "em"     = @DI\Inject("doctrine.orm.entity_manager"),
     *      "mailer" = @DI\Inject("project.mailer")
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
        $ip = $event->getRequest()->getClientIp();
        if ($user instanceof UserInterface) {
            $lastLogin = $user->getLastLogin();
            if (empty($lastLogin) && (UserType::TENANT === $user->getType())) {
                $this->mailer->sendWelcomeEmailToUser($user);
            }
            $user->setLastLogin(new \DateTime());
            $user->setlastIp($ip);
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
