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
        return array();
    }

    /**
     * @Template()
     */
    public function infoAction()
    {
        $user = $this->getUser();
        $em = $this->get('doctrine')->getManager();
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(array(
            'tenant' => $user->getId(),
            'status' => ContractStatus::ACTIVE,
        ));
        $status = (!empty($contract)) ? true : false;

        return array('status' => $status);
    }
}
