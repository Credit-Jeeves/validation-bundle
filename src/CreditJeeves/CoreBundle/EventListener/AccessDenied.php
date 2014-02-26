<?php

namespace CreditJeeves\CoreBundle\EventListener;

use CreditJeeves\DataBundle\Enum\UserType;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @Service("core.event_listener.kernel.access_denied")
 *
 * @Tag(
 *      "kernel.event_listener",
 *       attributes = {
 *          "event" = "kernel.exception",
 *          "method" = "onKernelException"
 *      }
 * )
 */
class AccessDenied
{

    protected $router;

    protected $user;

    /**
     * @InjectParams({
     *      "router"                 = @Inject("router"),
     *      "securityContext"        = @Inject("security.context")
     * })
     */
    public function __construct(Router $router, SecurityContextInterface $securityContext)
    {
        $this->router = $router;
        if ($token = $securityContext->getToken()) {
            $this->user = $token->getUser();
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($exception instanceof AccessDeniedHttpException)
        {
            $type = ($this->user)? $this->user->getType(): '';

            switch ($type) {
                case UserType::APPLICANT:
                    $route = $this->router->generate('applicant_homepage');
                    break;
                case UserType::DEALER:
                    $route = $this->router->generate('dealer_homepage');
                    break;
                case UserType::ADMIN:
                    $route = $this->router->generate('sonata_admin_dashboard');
                    break;
                case UserType::TETNANT:
                    $route = $this->router->generate('tenant_homepage');
                    break;
                case UserType::LANDLORD:
                    $route = $this->router->generate('landlord_homepage');
                    break;
                default:
                    $route = $this->router->generate('fos_user_security_login');
                    break;
            }


            $event->setResponse(new RedirectResponse($route));
            $event->stopPropagation();
        }
    }
}
