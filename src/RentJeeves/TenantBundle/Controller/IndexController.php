<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IndexController extends Controller
{
    /**
     * @Route("/", name="tenant_homepage")
     * @Template()
     */
    public function indexAction()
    {
        $twig = $this->container->get('twig');
        $twig->addGlobal(
            'alertMessages',
            array(
                '1' => $this->get('translator')->trans('rj.task.firstRent'),
            )
        );

        return array();
    }

    /**
     * @Template()
     */
    public function infoAction()
    {
        return array();
    }
}
