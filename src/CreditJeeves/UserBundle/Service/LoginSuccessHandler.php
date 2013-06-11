<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;

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
     * @var SecurityContext
     */
    protected $security;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
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
        $User = $token->getUser();
        $sType = $User->getType();

        switch ($sType) {
            case UserType::APPLICANT:
                $this->container->get('core.session.applicant')->setUser($User);
                $url = $this->container->get('router')->generate($sType.'_homepage');
                break;
            case UserType::DEALER:
                $this->container->get('core.session.dealer')->setUser($User);
                $url = $this->container->get('router')->generate($sType.'_homepage');
                break;
            case UserType::ADMIN:
                $this->container->get('core.session.admin')->setUser($User);
                $url = $this->container->get('router')->generate('sonata_admin_dashboard');
                break;
            case UserType::TETNANT:
                $this->container->get('core.session.tenant')->setUser($User);
                $url = $this->container->get('router')->generate('tenant_homepage');
                break;
            case UserType::LANDLORD:
                $this->container->get('core.session.landlord')->setUser($User);
                $url = $this->container->get('router')->generate('landlord_homepage');
                break;
        }
        return new RedirectResponse($url);
    }
}
