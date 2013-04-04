<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\User;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected $container;
    
    protected $security;
    
    /**
    * 
    * @param ContainerInterface $container
    * @param SecurityContext $security
    */
    public function __construct(ContainerInterface $container, SecurityContext $security)
    {
        $this->container = $container;
        $this->security = $security;
    }
    
    /**
    * 
    * @param Request $request
    * @param TokenInterface $token
    */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $User = $this->security->getToken()->getUser();
        $sType = $User->getType();
        switch ($sType) {
            case 'applicant':
                $this->container->get('core.session.applicant')->setUser($User);
                break;
            case 'dealer':
                $this->container->get('core.session.dealer')->setUser($User);
                break;
            case 'admin':
                //$User()->setRoles(array('ROLE_USER', 'ROLE_ADMIN'));
                //$User->persist();
                $this->container->get('core.session.admin')->setUser($User);
                break;
        }
        $url = $this->container->get('router')->generate($sType.'_homepage');
        $response = new RedirectResponse($url);
        return $response;
    }
}
