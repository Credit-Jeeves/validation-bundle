<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Alex
 * @Route(service="component.controller.footer")
 */
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
        return array(
            'sHost' => $sHost,
            );
    }
}
