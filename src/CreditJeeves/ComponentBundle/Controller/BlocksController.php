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
        $sEmail = $this->getUser()->getEmail();
        $Lead = $this->get('core.session.applicant')->getLead();
        $sGroup = $Lead->getGroup()->getName();
        return array(
            'sEmail' => $sEmail,
            'sGroup' => $sGroup,
            );
    }
}
