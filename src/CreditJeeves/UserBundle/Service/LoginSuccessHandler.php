<?php
namespace CreditJeeves\UserBundle\Service;

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

  public function __construct(ContainerInterface $container, SecurityContext $security)
  {
    $this->container = $container;
    $this->security = $security;
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token)
  {
//     $user = $this->security->getToken()->getUser();
//     echo $user->getUsername();
    $url = $this->container->get('router')->generate('homepage_dealer');
    $url = $this->container->get('router')->generate('homepage_applicant');
    $url = $this->container->get('router')->generate('homepage_admin');
    $response = new RedirectResponse($url);

    return $response;
  }

}