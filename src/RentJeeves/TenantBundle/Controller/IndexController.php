<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\DataBundle\Enum\ContractStatus;

class IndexController extends Controller
{
    /**
     * @Route("/", name="tenant_homepage")
     * @Template()
     */
    public function indexAction()
    {
        $tenant = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        $emContract = $em->getRepository('RjDataBundle:Contract');
        $allContracts = $emContract->getCountByStatus($tenant);
        
        $pendingContracts = $emContract->getCountByStatus($tenant, ContractStatus::PENDING);
        $activeContracts = $emContract->getCountByStatus($tenant, ContractStatus::CURRENT);
        
        if ($allContracts === $pendingContracts) {
            $status = ContractStatus::PENDING;
        } elseif ($activeContracts > 0) {
            $status = ContractStatus::CURRENT;
        } else {
            $status = ContractStatus::APPROVED;
        }
        $isReporting = $activeContracts = $em->getRepository('RjDataBundle:Contract')
                                ->countReporting($tenant);
        return array(
            'status' => $status,
            'reporting' => $isReporting,
            'user' => $tenant,
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
}
