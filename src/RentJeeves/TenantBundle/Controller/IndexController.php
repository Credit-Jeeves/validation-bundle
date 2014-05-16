<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\Contract;
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

        return array(
            'user'                      => $this->getUser(),
        );
    }

    /**
     * @Template()
     */
    public function paymentReportingAction()
    {
        $tenant = $this->getUser();
        $isReporting = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->countReporting($tenant);

        return array('isReporting' => $isReporting);
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
