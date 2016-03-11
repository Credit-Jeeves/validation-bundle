<?php

namespace RentJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FooterController extends Controller
{
    /**
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $sHost = $this->container->getParameter('server_name');
        $today = new \DateTime();

        return [
            'sHost' => $sHost,
            'currentYear' => $today->format('Y'),
        ];
    }
}
