<?php
namespace CreditJeeves\UserBundle\Controller;

use FOS\UserBundle\Controller\ResettingController as Base;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResettingController extends Base
{
    public function resetAction($token)
    {
        $return = parent::resetAction($token);

        if ($return instanceof RedirectResponse) {
            return $this->container->get('user.service.login_success_handler')
                ->onAuthenticationSuccess(
                    $this->container->get('request'),
                    $this->container->get('security.context')->getToken()
                );
        }
        return $return;
    }
}
