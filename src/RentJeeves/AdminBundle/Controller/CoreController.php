<?php

namespace RentJeeves\AdminBundle\Controller;

use RentJeeves\AdminBundle\Form\RentalReportType;
use RentJeeves\CoreBundle\Report\RentalReport;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\RentalReportFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;
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
        $rentalReportData = new RentalReportData();
        $reportType = $this->createForm(new RentalReportType(), $rentalReportData);
        $reportType->handleRequest($request);

        if ($reportType->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $propertyManagement = $this->container->getParameter('property_management');

            /** @var RentalReport $report */
            $report = RentalReportFactory::getReport($rentalReportData, $em, $propertyManagement);
            $report->build($rentalReportData);

            if ($report->isEmpty()) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans(
                        'admin.report.notice',
                        ['%m%' => $rentalReportData->getMonth()->format('m/Y')]
                    )
                );
            } else {
                $result = $this->get('jms_serializer')->serialize($report, $report->getSerializationType());
                $response = new Response($result, 200);
                $attachment = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $report->getReportFilename()
                );
                $response->headers->set('Content-Disposition', $attachment);
                return $response;
            }
        }

        return [
            'rentalReport' => $reportType->createView(),
        ];
    }
}
