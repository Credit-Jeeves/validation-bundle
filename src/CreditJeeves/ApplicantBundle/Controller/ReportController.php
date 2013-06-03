<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportController extends Controller
{
    /**
     * @Route("/report", name="applicant_report")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        // TODO add check for admin

        /* @var User $User */
        $User = $this->get('core.session.applicant')->getUser();

//        $Report = $User->getReportsD2c()->last();

        /** @var Order $Order */
        if ($Order = $User->getOrders()->last()) {
            /** @var Operation $Operation */
            if ($Operation = $Order->getOperations()->last()) {
                $Report = $Operation->getReportD2c();
            } else {
                return $this->redirect($this->generateUrl('core_report_get_d2c'));
            }
        } else {
            return $this->createNotFoundException('Order does not found');
        }

        return array(
            'Report' => $Report,
            'sSupportEmail' => $this->container->getParameter('support_email'),
            'sSupportPhone' => $this->container->getParameter('support_phone'),
        );
    }
}
