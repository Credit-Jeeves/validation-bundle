<?php

namespace CreditJeeves\CoreBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SessionController extends Controller
{
    /**
     * @Template()
     */
    public function jsAction()
    {
        $nameSession = $this->get('session')->getName();
        $isLogin = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');

        return array(
            'sesssionExpirationName' => $nameSession.'_expiration_date',
            'sessionName'            => $nameSession,
            'isLogin'               => $isLogin
        );
    }
}
