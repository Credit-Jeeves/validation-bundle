<?php

namespace RentJeeves\LandlordBundle\Controller;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;
use RentJeeves\LandlordBundle\Form\BaseOrderReportType;

/**
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     name="landlord_reports"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(new BaseOrderReportType($user, $group));

        if ($this->get('request')->getMethod() == 'POST') {
            $formBaseOrder->handleRequest($this->get('request'));
            if ($formBaseOrder->isValid()) {
                //@TODO make code for create and download reports by type
            }
        }
        return array(
            'settings'           => $user->getSettings(),
            'formBaseOrder'      => $formBaseOrder->createView()
        );
    }
}
