<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BlocksController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function infoAction()
    {
        $isAdmin = $this->get('core.session.applicant')->isAdmin();
        $sEmail = $this->get('core.session.applicant')->getUser()->getEmail();
        return array(
            'sEmail' => $sEmail,
            'isAdmin' => $isAdmin
            );
    }
}
