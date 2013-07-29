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
        $allContracts = $em->getRepository('RjDataBundle:Contract')->getCountByStatus($tenant, $status = NULL);
        $pendingContracts = $em->getRepository('RjDataBundle:Contract')->getCountByStatus($tenant, ContractStatus::PENDING);
        $activeContracts = $em->getRepository('RjDataBundle:Contract')->getCountByStatus($tenant, ContractStatus::ACTIVE);
        
        if($allContracts === $pendingContracts) {
            $status = 'new';
        } else if ($activeContracts > 0) {
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
        $activeContracts = $em->getRepository('RjDataBundle:Contract')->getCountByStatus($tenant, ContractStatus::ACTIVE);
        
        if ($activeContracts > 0) {
            $status = true;
        } else {
            $status = false;
        }

        return array('status' => $status);
    }
}
