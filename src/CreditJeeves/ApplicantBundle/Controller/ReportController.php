<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 */
class ReportController extends Controller
{
    /**
     * @Route("/report", name="applicant_report")
     * @Route("/tenant/report", name="tenant_report")
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
            if ($Operation = $User->getLastCompleteOperation(OperationType::REPORT)) {
                $Report = $Operation->getReportD2c();
            } else {
                return $this->redirect($this->generateUrl('core_report_get_d2c'));
            }
        } else {
            return $this->createNotFoundException('Order does not found');
        }

        return array(
            'parentTemplate' => (UserType::TETNANT == $this->getUser()->getType() ?
                'TenantBundle::base.html.twig' : 'ApplicantBundle::base.html.twig'),
            'Report' => $Report,
            'sSupportEmail' => $this->container->getParameter('support_email'),
            'sSupportPhone' => $this->container->getParameter('support_phone'),
        );
    }
}
