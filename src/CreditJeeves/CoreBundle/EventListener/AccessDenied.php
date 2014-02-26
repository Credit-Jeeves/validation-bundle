<?php

namespace CreditJeeves\CoreBundle\EventListener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * @Service("core.event_listener.kernel.access_denied")
 *
 * @Tag(
 *      "kernel.event_listener",
 *       attributes = {
 *          "event" = "kernel.exception",
 *          "method" = "handleAccessDeniedException"
 *      }
 * )
 */
class AccessDenied
{
    protected $session;

    protected $router;

    protected $translator;

    /**
     * @InjectParams({
     *      "session"           = @Inject("session"),
     *      "router"            = @Inject("router"),
     *      "translator"        = @Inject("translator")
     * })
     */
    public function __construct(Session $session, Router $router, $translator)
    {
        $this->session = $session;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function handleAccessDeniedException(GetResponseForExceptionEvent $event)
    {
        echo "hi";exit;
        if ($event->getException()->getMessage() == 'Access Denied')
        {
            $title = $this->session->getFlashBag()->set('message_title', $this->translator->trans('access.denied'));
            $text = $this->session->getFlashBag()->get('message_body', $this->translator->trans('access.denied.description'));

            $route = $this->router->generate('public_message_flash');
            $event->setResponse(new RedirectResponse($route));
        }
    }
}
