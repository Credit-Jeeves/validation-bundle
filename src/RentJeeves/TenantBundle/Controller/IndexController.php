<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\Tenant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RentJeeves\DataBundle\Enum\ContractStatus;

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
        $isReporting = $this->get('doctrine')->getRepository('RjDataBundle:Contract')
                                ->countReporting($tenant);

        return array(
            'reporting'                 => $isReporting,
            'user'                      => $this->getUser(),
        );
    }

    /**
     * @Template()
     */
    public function infoAction()
    {
        $tenant = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        $activeContracts = $em->getRepository('RjDataBundle:Contract')
                                ->getCountByStatus($tenant, ContractStatus::CURRENT);
        
        if ($activeContracts > 0) {
            $status = true;
        } else {
            $status = false;
        }

        return array('status' => $status);
    }

    /**
     * @Route(
     *  "/bureau/{action}",
     *  name="tenant_reporting_start"
     * )
     *
     * @return array
     */
    public function startBureauReporting($action)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $contracts = $user->getContracts();
        foreach ($contracts as $contract) {
            if ($action == 'start') {
                $contract->setReporting(true);
            } else {
                $contract->setReporting(false);
            }
            $em->persist($contract);
            $em->flush();
        }
        $url = $this->container->get('router')->generate('tenant_homepage');
        return new RedirectResponse($url);
    }
}
