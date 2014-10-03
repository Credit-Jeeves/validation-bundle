<?php

namespace RentJeeves\OAuthServerBundle\Security;

use OAuth2\OAuth2;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("oauth_server.logout_handler")
 */
class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     *
     * @DI\InjectParams({
     *   "router" = @DI\Inject("router"),
     * })
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
    }
}
