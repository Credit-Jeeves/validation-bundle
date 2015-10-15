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
//        $nCountReports = $this->get('core.session.applicant')->getUser()->getReportsD2c()->count();
        $nCountReports = 0;
        $sRouteName = $this->getRequest()->get('_route');

        //echo $sRouteName;
        return array(
            'sRouteName' => $sRouteName,
            'nReport' => $nCountReports
        );
    }
}
