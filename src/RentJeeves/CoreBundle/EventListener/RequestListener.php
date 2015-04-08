<?php

namespace RentJeeves\CoreBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service("core.event_listener.kernel.request")
 *
 * @DI\Tag("kernel.event_listener", attributes = {
 *      "event" = "kernel.request",
 *      "method" = "onKernelRequest",
 *      "priority" = 9,
 * })
 *
 * For correct work of the current listener
 * its priority should be larger
 * than firewall`s priority for "kernel.request" (it is 8)
 * @see \Symfony\Component\Security\Http\Firewall
 *
 * @link https://credit.atlassian.net/browse/RT-1246 a description of why it's done
 */
class RequestListener
{
    const PARAMETER_HOLDING_ID = 'holding_id';
    const PARAMETER_RESIDENT_ID = 'resident_id';

    /**
     * @var SecurityContextInterface
     */
    private $context;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param SecurityContextInterface $context
     * @param Session                  $session
     *
     * @DI\InjectParams({
     * "context" = @DI\Inject("security.context"),
     * "session" = @DI\Inject("session"),
     * })
     */
    public function __construct(SecurityContextInterface $context, Session $session)
    {
        $this->context = $context;
        $this->session = $session;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null === $this->context->getToken()) {
            $request = $event->getRequest();
            $this->getDefiniteParametersAndAddToSession($request);
        }
    }

    /**
     * @param Request $request
     */
    private function getDefiniteParametersAndAddToSession(Request $request)
    {
        if ($holdingId = $request->query->get(self::PARAMETER_HOLDING_ID)) {
            $this->session->set(self::PARAMETER_HOLDING_ID, $holdingId);
        }
        if ($residentId = $request->query->get(self::PARAMETER_RESIDENT_ID)) {
            $this->session->set(self::PARAMETER_RESIDENT_ID, $residentId);
        }
    }
}
