<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportController extends Controller
{
    /**
     * @Route("/report", name="tenant_report")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        // TODO add check for admin
        /* @var User $User */
        $User = $this->getUser();

        /** @var Order $Order */
        if ($Order = $User->getLastCompleteOrder()) {
            /** @var Operation $Operation */
            if ($Operation = $Order->getOperations()->last()) {
                $Report = $Operation->getReportD2c();
            } else {
                return $this->redirect($this->generateUrl('core_report_get_d2c'));
            }
        } else {
            throw $this->createNotFoundException('Order does not found');
        }
        if (empty($Report)) {
            throw $this->createNotFoundException('Report does not found');
        }

        return array(
            'Report' => $Report,
            'sSupportEmail' => $this->container->getParameter('support_email'),
            'sSupportPhone' => $this->container->getParameter('support_phone'),
        );
    }
}
