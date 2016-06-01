<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\OperationRepository;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\RentalReport;
use RentJeeves\CoreBundle\Report\RentalReportData;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType as BureauReportType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RentalReportController extends BaseController
{
    const NOTIFICATION_LANDLORD = 'landlord_email';
    const NOTIFICATION_TENANT = 'tenant_email';

    /**
     * @Route("report", name="sonata_admin_rental_report")
     *
     * @param Request $request
     * @return array
     */
    public function reportAction(Request $request)
    {
        $rentalReportData = new RentalReportData();
        $reportType = $this->createForm('rental_report', $rentalReportData);
        $reportType->handleRequest($request);

        if ($reportType->isValid()) {
            $this->get('soft.deleteable.control')->disable();
            /** @var RentalReport $report */
            $report = $this->get('rental_report.factory')->getReport($rentalReportData);
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

        return $this->render(
            'AdminBundle:RentalReport:report.html.twig',
            [
                'rentalReport' => $reportType->createView()
            ]
        );
    }

    /**
     * @Route("report_transunion", name="sonata_admin_rental_report_transunion")
     *
     * @param Request $request
     * @return array
     */
    public function reportTransUnionAction(Request $request)
    {
        $rentalReportData = new RentalReportData();
        $reportType = $this->createForm('rental_report', $rentalReportData);
        $reportType->handleRequest($request);
        $rentalReportData->setBureau(CreditBureau::TRANS_UNION);
        $rentalReportData->setType(BureauReportType::NEGATIVE);

        $reportData = [];
        if ($reportType->isValid()) {
            /** @var RentalReport $report */
            $report = $this->get('rental_report.factory')->getReport($rentalReportData);
            $report->build($rentalReportData);
            $reportData = $report->getRecords();

            if ($report->isEmpty()) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans(
                        'admin.report.notice',
                        ['%m%' => $rentalReportData->getMonth()->format('m/Y')]
                    )
                );
            }
        }

        return $this->render(
            'AdminBundle:RentalReport:report_transunion.html.twig',
            [
                'rentalReport' => $reportType->createView(),
                'reportData' => $reportData,
            ]
        );
    }

    /**
     * @Route("report_experian", name="sonata_admin_rental_report_experian")
     *
     * @param Request $request
     * @return array
     */
    public function reportExperianAction(Request $request)
    {
        $rentalReportData = new RentalReportData();
        $reportType = $this->createForm('rental_report', $rentalReportData);
        $reportType->handleRequest($request);
        $rentalReportData->setBureau(CreditBureau::EXPERIAN);
        $rentalReportData->setType(BureauReportType::NEGATIVE);

        $reportData = [];
        if ($reportType->isValid()) {
            /** @var RentalReport $report */
            $em = $this->get('doctrine.orm.default_entity_manager');
            $contracts = $em->getRepository('RjDataBundle:Contract')->getContractsForExperianNegativeReport(
                $rentalReportData->getMonth(),
                $rentalReportData->getStartDate(),
                $rentalReportData->getEndDate()
            );

            /** @var OperationRepository $operationRepo */
            $operationRepo = $em->getRepository('DataBundle:Operation');
            $reportData = [];
            foreach ($contracts as $contract) {
                $reportData[] = [
                    'contract' => $contract,
                    'lastPaidFor' => $operationRepo->getLastContractPaidFor($contract)
                ];
            }
            if (count($reportData) == 0) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans(
                        'admin.report.notice',
                        ['%m%' => $rentalReportData->getMonth()->format('m/Y')]
                    )
                );
            }
        }

        return $this->render(
            'AdminBundle:RentalReport:report_experian.html.twig',
            [
                'rentalReport' => $reportType->createView(),
                'reportData' => $reportData,
            ]
        );
    }

    /**
     * @Route(
     *     "rental_report_send",
     *     name="sonata_admin_rental_report_send",
     *     options={"expose"=true}
     * )
     *
     * @return array
     */
    public function sendNotificationAction(Request $request)
    {
        $action = $request->request->get('action');
        $idx = $request->request->get('idx');
        $month = $request->request->get('month');
        $monthName = (new \DateTime("2015-{$month}-01"))->format('F');

        $em = $this->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->getContractsByIds($idx);

        /** @var Mailer $mailer */
        $mailer = $this->get('project.mailer');
        switch ($action) {
            case self::NOTIFICATION_TENANT:
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    $mailer->sendLateReportingReviewEmailToTenant($contract->getTenant(), $monthName);
                }
                break;
            case self::NOTIFICATION_LANDLORD:
                /**
                 * Step 1: organize contracts into array groupId => []
                 * <code>
                 * [
                 *     1 => [
                 *          'contracts' => [],
                 *          'landlords' => []
                 *          ]
                 * ]
                 * where
                 *     1 - groupID,
                 *     tenants = an array of late tenants,
                 *     landlords = an array of all landlords of late contract group
                 * </code>
                 */
                $contractsByGroup = [];
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    $contractsByGroup[$contract->getGroupId()]['contracts'][] = $contract;
                    if (!isset($contractsByGroup[$contract->getGroupId()]['landlords'])) {
                        $contractsByGroup[$contract->getGroupId()]['landlords'] =
                            $contract->getGroup()->getGroupAgents();
                    }
                }

                foreach ($contractsByGroup as $groupId => $notificationData) {
                    // Send late alerts to each landlord of the late contract group
                    /** @var Landlord $landlord */
                    foreach ($notificationData['landlords'] as $landlord) {
                        $mailer->sendLateReportingReviewEmailToLandlord(
                            $landlord,
                            $notificationData['contracts'],
                            $monthName
                        );
                    }
                }
                break;
            default:
                return new Response(sprintf('Unknown action \'%s\' requested.', $action), 400);
        }

        return new Response();
    }
}
