<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserType;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SummaryController extends Controller
{
    /**
     * @Route("/summary", name="tenant_summary")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if (UserIsVerified::PASSED != $user->getIsVerified()) {
            throw $this->createNotFoundException('Verification do not passed');
        }

        $sEmail = $user->getEmail();
        $Report  = $this->getReport();

        if (!$Report) {
            return $this->forward('ExperianBundle:Report:get');
        }

        $Score = $this->getScore();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            'Score' => $Score,
            'User' => $user,
        );
    }
}
