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
        $activeContracts = $emContract->getCountByStatus($tenant, ContractStatus::ACTIVE);

        if ($allContracts === $pendingContracts) {
            $status = 'new';
        } elseif ($activeContracts > 0) {
            $status = 'active';
        } else {
            $status = 'approved';
        }

        return array('status' => $status);
    }

    /**
     * @Template()
     */
    public function infoAction()
    {
        $tenant = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        $activeContracts = $em->getRepository('RjDataBundle:Contract')
                                ->getCountByStatus($tenant, ContractStatus::ACTIVE);
        
        if ($activeContracts > 0) {
            $status = true;
        } else {
            $status = false;
        }

        return array('status' => $status);
    }
}
