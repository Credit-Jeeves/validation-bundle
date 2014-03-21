<?php

namespace RentJeeves\AdminBundle\Controller;

use RentJeeves\CoreBundle\Report\ExperianRentalReport;
use RentJeeves\CoreBundle\Report\TransUnionRentalReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;
use RuntimeException;

class CoreController extends BaseController
{
    /**
     * @Route("dashboard", name="sonata_admin_dashboard")
     * @Template()
     *
     * @return array
     */
    public function dashboardAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('contract_id', null);
        $request->getSession()->set('user_id', null);
        $request->getSession()->set('holding_id', null);
        $request->getSession()->set('landlord_id', null);
        $request->getSession()->set('group_id', null);
        $request->getSession()->set('property_id', null);
        return parent::dashboardAction();
    }

    /**
     * @Route("report/{type}/{month}/{year}", name="sonata_admin_report")
     * @Template()
     *
     * @return array
     */
    public function reportAction($type, $month, $year)
    {
        $em = $this->getDoctrine()->getManager();

        switch ($type) {
            case 'trans_union':
                $report = new TransUnionRentalReport($em, $month, $year);
                $serializationType = 'tu_rental1';
                break;
            case 'experian':
                $report = new ExperianRentalReport($em, $month, $year);
                $serializationType = 'csv';
                break;
            default:
                throw new RuntimeException(sprintf('Given report type \'%s\' does not exist', $type));
        }

        $result = $this->get('jms_serializer')->serialize($report, $serializationType);

        return new Response($result, 200);
    }
}
