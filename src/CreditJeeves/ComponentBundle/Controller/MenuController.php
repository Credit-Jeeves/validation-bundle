<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Alex
 * @Route(service="component.controller.menu")
 */
class MenuController extends Controller
{
    /**
     * @Template()
     *
     * @return array
     */
    public function tabsAction()
    {
        $nCountReports = $this->getUser()->getReportsD2c()->count();
        $sRouteName = $this->getRequest()->get('_route');
        return array(
            'sRouteName' => $sRouteName,
            'nReport' => $nCountReports
            );
    }
}
