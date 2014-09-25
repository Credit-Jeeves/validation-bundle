<?php

namespace CreditJeeves\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class SecurityController extends BaseController
{

    /**
     * @Route("/login", name="fos_user_security_login")
     * @Route("/invite/login/{inviteCode}", name="fos_user_security_login_invite")
     * @Route("/login/defence/{defenseMessage}", name="fos_user_security_login_defence")
     */
    public function loginAction($inviteCode = null, $defenseMessage = null)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        $routeName = $request->get('_route');

        if (!empty($inviteCode) && $routeName === 'fos_user_security_login_invite') {
            $session->set('inviteCode', $inviteCode);
        }

        if ($routeName === 'fos_user_security_login_defence') {
            $this->container->get('security.context')->setToken(
                new AnonymousToken('main', 'anon.')
            ); //do logout user
        }

        return parent::loginAction();
    }
}
