<?php
namespace CreditJeeves\UserBundle\EventListener;

use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("user.event_listener.login_success_handler")
 * @DI\Tag("kernel.event_subscriber")
 */
class LoginSuccessListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "logger"    = @DI\Inject("logger")
     * })
     *
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => ['setUserSession', 255],
        ];
    }

    /**
     * @param AuthenticationEvent $event
     */
    public function setUserSession(AuthenticationEvent $event)
    {
        if ($token = $event->getAuthenticationToken()) {

            /** @var User $user */
            $user = $token->getUser();
            $sType = $user->getType();

            $this->logger->debug('Successful authentication for: ' . $user->getEmail());

            switch ($sType) {
                case UserType::APPLICANT:
                    $this->logger->debug('Setting user session for applicant');
                    $this->container->get('core.session.applicant')->setUser($user);
                    break;
                case UserType::DEALER:
                    $this->logger->debug('Setting user session for dealer');
                    $this->container->get('session')->getFlashBag()->add(
                        'notice',
                        'Please log in using the Dealership link to the right.'
                    );
                    break;
                case UserType::ADMIN:
                    $this->logger->debug('Setting user session for  admin');
                    $this->container->get('core.session.admin')->setUser($user);
                    break;
                case UserType::TENANT:
                    $this->logger->debug('Setting user session for tenant');
                    $this->container->get('core.session.tenant')->setUser($user);
                    break;
                case UserType::LANDLORD:
                    $this->logger->debug('Setting user session for landlord');
                    $this->container->get('core.session.landlord')->setUser($user);
                    break;
            }
        }
    }
}
