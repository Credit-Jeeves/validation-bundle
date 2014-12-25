<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class IndexController extends Controller
{
    /**
     * @Route("/", name="tenant_homepage")
     * @Template()
     */
    public function indexAction()
    {
        /**
         * @var $tenant Tenant
         */
        $tenant = $this->getUser();
        //For this functional need show unit which was removed
        $this->get('soft.deleteable.control')->disable();
        $mobile = true;
        if($this->isMobile()) {
            return $this->render('TenantBundle:Index:index.mobile.html.twig', array('user' => $tenant)); //mobile template override
        }else {
            return array(
                'user' => $tenant,
            );
        }
    }

    /**
     * @Template()
     */
    public function paymentReportingAction()
    {
        /** @var $tenant Tenant */
        $tenant = $this->getUser();
        /** @var ContractRepository $contractRepo */
        $contractRepo = $this->getDoctrine()->getRepository('RjDataBundle:Contract');
        $isReporting = $contractRepo->countReporting($tenant);
        $countReportingIsOffContracts = $contractRepo->countContractsWithReportingIsOff($tenant);
        $countCurrentContracts = $contractRepo->countTenantContractsByStatus($tenant, ContractStatus::CURRENT);

        // if ANY associated contracts have groups with reportingIsOff = true, turn off for now
        $reportingIsOff = $countReportingIsOffContracts > 0;
        $hasAccessToOptInReporting = $countCurrentContracts > 0 && !$reportingIsOff;

        return array(
            'isReporting' => $isReporting,
            'reportingIsOff' => $reportingIsOff,
            'hasAccessToOptInReporting' => $hasAccessToOptInReporting,
        );
    }

    /**
     * @Route(
     *  "/bureau/start",
     *  name="tenant_reporting_start"
     * )
     *
     * @return array
     */
    public function startBureauReporting(Request $request)
    {
        $bureaus = $request->request->get('reporting', []);
        $includeExperian = in_array('experian', $bureaus);
        $includeTransUnion = in_array('trans_union', $bureaus);

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $contracts = $user->getContracts();

        /** @var $contract Contract */
        foreach ($contracts as $contract) {
            if ($includeExperian) {
                $contract->setReportToExperian(true);
                $contract->setExperianStartAt($now);
            }
            if ($includeTransUnion) {
                $contract->setReportToTransUnion(true);
                $contract->setTransUnionStartAt($now);
            }
        }

        $em->flush();
        $url = $this->container->get('router')->generate('tenant_summary');

        return new RedirectResponse($url);
    }
}
