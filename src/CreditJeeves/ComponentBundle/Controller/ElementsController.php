<?php

namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ElementsController extends Controller
{
    /**
     * @Template
     *
     * @return array
     */
    public function infoAction()
    {
        $sEmail = $this->getUser()->getEmail();
        return array('sEmail' => $sEmail);
    }
}
