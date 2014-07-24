<?php
namespace RentJeeves\TenantBundle\EventListener;

use CreditJeeves\DataBundle\Enum\UserType;
use FOS\UserBundle\Model\UserInterface;
use RentJeeves\DataBundle\Entity\Tenant;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Doctrine\ORM\EntityManager;
use RentJeeves\LandlordBundle\Accounting\AccountingContract;

/**
 * @DI\Service("user.event_listener.tenant.on_login")
 *
 * @DI\Tag("kernel.event_subscriber")
 */
class SecurityOnLogin implements EventSubscriberInterface
{
    protected $session;

    protected $em;

    protected $accounting;

    /**
     * @DI\InjectParams({
     *      "em"          = @DI\Inject("doctrine.orm.default_entity_manager"),
     *      "accounting"  = @DI\Inject("accounting.contract"),
     *      "session"     = @DI\Inject("session")
     * })
     */
    public function __construct(EntityManager $em, AccountingContract $accounting, $session)
    {
        $this->session = $session;
        $this->em = $em;
        $this->accounting = $accounting;
    }

    public static function getSubscribedEvents()
    {
        return array(
                SecurityEvents::INTERACTIVE_LOGIN => array(
                    'onSecurityInteractiveLoginTenant',
                    255
                ),
        );
    }

    public function onSecurityInteractiveLoginTenant(InteractiveLoginEvent $event)
    {
        /**
         * @var $user Tenant
         */
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        if ($user->getType() !== UserType::TENANT) {
            return;
        }

        $this->processAccountingContract($user);
    }

    protected function processAccountingContract($user)
    {
        if (!$inviteCode = $this->session->get('inviteCode')) {
            return;
        }

        $fromUser = $this->em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'invite_code' => $inviteCode
            )
        );

        if (!$fromUser) {
            return;
        }

        $this->accounting->moveContractToNewUser($fromUser, $user);
    }
}
