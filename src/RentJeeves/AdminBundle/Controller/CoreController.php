<?php

namespace RentJeeves\AdminBundle\Controller;

use RentJeeves\CoreBundle\Report\ExperianRentalReport;
use RentJeeves\CoreBundle\Report\TransUnionRentalReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;
use DateTime;
use RuntimeException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
     * @Route("report", name="sonata_admin_rental_report")
     * @Template()
     *
     * @return array
     */
    public function reportAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $type = $request->request->get('type');
            $month = $request->request->get('month');
            $year = $request->request->get('year');

            $em = $this->getDoctrine()->getManager();
            $params = $this->container->getParameter('property_management');

            switch ($type) {
                case 'trans_union':
                    $report = new TransUnionRentalReport($em, $month, $year, $params);
                    break;
                case 'experian':
                    $report = new ExperianRentalReport($em, $month, $year);
                    break;
                default:
                    throw new RuntimeException(sprintf('Given report type "\'%s\'" does not exist', $type));
            }

            $result = $this->get('jms_serializer')->serialize($report, $report->getSerializationType());

            $response = new Response($result, 200);
            $attachment = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $report->getReportFilename()
            );
            $response->headers->set('Content-Disposition', $attachment);

            return $response;
        }

        $months = array();
        foreach (range(1, 12) as $month) {
            $months[$month] = date('F', strtotime("2000-{$month}-1"));
        }

        $now = new DateTime();
        $years = array($now->format('Y'), $now->modify('-1 year')->format('Y'));

        return array(
            'months' => $months,
            'years' => $years,
        );
    }
}
