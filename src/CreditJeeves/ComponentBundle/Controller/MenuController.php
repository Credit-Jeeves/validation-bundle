<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
    public function tabsAction(Request $request)
    {
        return [
            'routeName' => $request->get('_route'),
        ];
    }
}
