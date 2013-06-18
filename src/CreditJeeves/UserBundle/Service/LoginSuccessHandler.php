<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\User;
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
            case 'applicant':
                $this->container->get('core.session.applicant')->setUser($User);
                $url = $this->container->get('router')->generate($sType.'_homepage');
                break;
            case 'dealer':
                $url = $this->container->get('router')->generate('fos_user_security_login');
                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in using the Dealership link to the right.'
                );
                break;
            case 'admin':
                $this->container->get('core.session.admin')->setUser($User);
                $url = $this->container->get('router')->generate('sonata_admin_dashboard');
                break;
        }
        return new RedirectResponse($url);
    }
}
