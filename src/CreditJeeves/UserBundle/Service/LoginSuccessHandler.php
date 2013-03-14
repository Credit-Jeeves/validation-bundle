<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\UserBundle\Entity\User;

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
        $cjUser = $this->security->getToken()->getUser();
        $sType = $cjUser->getType();
        switch ($sType) {
            case 'applicant':
                $this->prepareApplicant($cjUser);
                break;
            case 'dealer':
                break;
            case 'admin':
                break;
        }
        $url = $this->container->get('router')->generate($sType.'_homepage');
        $response = new RedirectResponse($url);
        return $response;
    }

    private function prepareApplicant(User $cjUser)
    {

    }
}
