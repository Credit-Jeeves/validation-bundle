<?php

namespace RentJeeves\PublicBundle\Service;

use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @DI\Service("common.login.manager")
 */
class CommonLoginManager implements CommonLoginManagerInterface
{
    protected $loginManager;
    protected $firewallName;
    protected $loginSuccessHandler;
    protected $request;
    protected $securityContext;

    /**
     * @DI\InjectParams({
     *     "loginManager" = @DI\Inject("fos_user.security.login_manager"),
     *     "firewallName" = @DI\Inject("%fos_user.firewall_name%"),
     *     "loginSuccessHandler" = @DI\Inject("user.service.login_success_handler"),
     *     "request" = @DI\Inject("request", strict=false),
     *     "securityContext" = @DI\Inject("security.context")
     * })
     */
    public function __construct($loginManager, $firewallName, $loginSuccessHandler, $request, $securityContext)
    {
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
        $this->loginSuccessHandler = $loginSuccessHandler;
        $this->request = $request;
        $this->securityContext = $securityContext;
    }

    public function login(User $user)
    {
        $this->loginManager->loginUser($this->firewallName, $user);

        $this->loginSuccessHandler->onAuthenticationSuccess(
            $this->request,
            $this->securityContext->getToken()
        );
    }

    public function loginAndRedirect(User $user, $url)
    {
        $response = new RedirectResponse($url);
        $this->loginManager->loginUser($this->firewallName, $user, $response);

        $this->loginSuccessHandler->onAuthenticationSuccess(
            $this->request,
            $this->securityContext->getToken()
        );

        return $response;
    }
}
