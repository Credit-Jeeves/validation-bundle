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
    public function infoAction($site = 'cj')
    {
        $sGroup = '';
        $isAdmin = false;
        $user = $this->getUser();
        $type =  $user->getType();
        if ($type == 'admin') {
            $isAdmin = true;
        }
        switch ($site) {
            case 'rj':
                $user = $this->get('core.session.tenant')->getUser();
                $sEmail = $this->get('core.session.tenant')->getUser()->getEmail();
                break;
            default:
                $user = $this->get('core.session.applicant')->getUser();
                $sEmail = $this->get('core.session.applicant')->getUser()->getEmail();
                $Lead = $this->get('core.session.applicant')->getLead();
                if ($Lead) {
                    $sGroup = $Lead->getGroup()->getName();
                }
                break;
        }
        return array(
            'sEmail' => $sEmail,
            'sGroup' => $sGroup,
            'isAdmin' => $isAdmin,
            'site' => $site,
        );
    }
}
