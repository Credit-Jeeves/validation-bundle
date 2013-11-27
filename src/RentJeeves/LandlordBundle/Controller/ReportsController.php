<?php

namespace RentJeeves\LandlordBundle\Controller;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;

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
     * @Method({"GET"})
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }

        return array(
            'settings' => $user->getSettings(),
        );
    }
}
