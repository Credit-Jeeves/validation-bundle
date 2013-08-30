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

        $type =  $user->getType();
        if ($type == 'admin') {
            $isAdmin = true;
        }

        return array(
            'isAdmin' => $isAdmin,
        );
    }
}
