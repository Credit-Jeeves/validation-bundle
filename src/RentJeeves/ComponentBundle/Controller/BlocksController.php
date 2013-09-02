<?php

namespace RentJeeves\ComponentBundle\Controller;

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
        $isAdmin = false;
        $user = $this->getUser();
        if (!$user) {
            return array(
                'isAdmin' => $isAdmin,
            );
        }

        if ($this->get('session')->get('observe_admin_id')) {
            $isAdmin = true;
        }

        return array(
            'isAdmin' => $isAdmin,
        );
    }

    /**
     * @Template
     *
     * @return array
     */
    public function passwordAction($formPath, $form)
    {
        return array('formPath' => $formPath, 'form' => $form);
    }
}
