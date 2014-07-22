<?php

namespace CreditJeeves\UserBundle\Controller;

use CreditJeeves\CoreBundle\Controller\ApplicantController;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ReportController extends Controller
{
    /**
     * @Route("/report", name="user_report")
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
            if ($Operation = $User->getLastCompleteReportOperation()) {
                $Report = $Operation->getReportD2c();
            } else {
                throw $this->createNotFoundException('Operation does not found');
            }

            if (empty($Report) || !$Report->getRawData()) {
                return $this->forward('ExperianBundle:Report:getD2c');
            }

        } else {
            throw $this->createNotFoundException('Order does not found');
        }

        return array(
            'Report' => $Report,
            'sSupportEmail' => $this->container->getParameter('support_email'),
            'sSupportPhone' => $this->container->getParameter('support_phone'),
        );
    }
}
