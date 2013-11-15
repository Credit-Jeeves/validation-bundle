<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
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
        $tenant = $this->getUser();
//         $contracts = $tenant->getContracts();
//         foreach ($contracts as $contract) {
//             $operations = $contract->getOperations();
//             foreach ($operations as $operation) {
//                 $orders = $operation->getOrders();
//                 foreach ($orders as $order) {
//                     echo $order->getOperations()->last()->getType();
//                 }
//             }
//         }
        //For this page need show unit each was removed
        $this->get('doctrine')->getFilters()->disable('softdeleteable');
        $isReporting = $this->get('doctrine')->getRepository('RjDataBundle:Contract')
                                ->countReporting($tenant);
        return array(
            'reporting' => $isReporting,
            'user' => $this->getUser(),
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
