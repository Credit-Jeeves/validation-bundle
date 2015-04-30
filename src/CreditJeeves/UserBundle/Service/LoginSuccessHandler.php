<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Model\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @DI\Service("user.service.login_success_handler")
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $sType = $user->getType();

        $url = $this->getRefererExceptLogin($request);

        switch ($sType) {
            case UserType::APPLICANT:
                $this->container->get('core.session.applicant')->setUser($user);
                $url = $url ?: $this->getRouter()->generate($sType . '_homepage');
                break;
            case UserType::DEALER:
                $url = $url ?: $this->getRouter()->generate('fos_user_security_login');
                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in using the Dealership link to the right.'
                );
                break;
            case UserType::ADMIN:
                $this->container->get('core.session.admin')->setUser($user);
                $url = $url ?: $this->getRouter()->generate('sonata_admin_dashboard');
                break;
            case UserType::TENANT:
                $this->container->get('core.session.tenant')->setUser($user);
                $url = $url ?: $this->getRouter()->generate('tenant_homepage');
                break;
            case UserType::LANDLORD:
                $this->container->get('core.session.landlord')->setUser($user);
                $url = $url ?: $this->generateLandlordUrl($user);
                break;
        }
        /**
         * @var $userLogAndDefence LogAndDefenceUser
         */
        $userLogAndDefence = $this->container->get('user.log_and_defence');
        $userLogAndDefence->signin($user->getEmail(), $status = 'success');

        if ($userLogAndDefence->isDefense($user)) {
            $url = $userLogAndDefence->getRedirectLinkForDefense();
        } else {
            $userLogAndDefence->clearDefense($user);
        }

        return new RedirectResponse($url);
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function generateLandlordUrl(User $user)
    {
        $url = $this->getRouter()->generate('landlord_homepage');
        $contracts = $user->getHolding()->getContracts();
        if (count($contracts) == 1 && ContractStatus::PENDING == $contracts->last()->getStatus()) {
            $url = $this->getRouter()->generate('landlord_tenants');
        }

        return $url;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getRefererExceptLogin(Request $request)
    {
        $url = $request->headers->get('referer');

        if ($this->getRouter()->getRouteCollection()->get('fos_user_security_login') != null) {
            $loginUrl = $this->getRouter()->generate('fos_user_security_login');
            if (false !== strstr($url, $loginUrl)) {
                $url = null;
            }
        }

        if ($this->getRouter()->getRouteCollection()->get('management_login') != null) {
            $loginUrlIframe = $this->getRouter()->generate('management_login');
            if (false !== strstr($url, $loginUrlIframe)) {
                $url = null;
            }
        }

        return $url;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }
}
