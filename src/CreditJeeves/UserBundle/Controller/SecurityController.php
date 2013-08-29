<?php

namespace CreditJeeves\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends BaseController
{

    /**
     * @Route("/login", name="fos_user_security_login")
     * @Route("/invite/login/{inviteCode}", name="fos_user_security_login_invite")
     */
    public function loginAction($inviteCode = NULL)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        if (!empty($inviteCode)) {
            $session->set('inviteCode', $inviteCode);
        }

        return parent::loginAction();
    }
}
