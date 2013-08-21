<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/ajax")
 * @author alex
 *
 */
class AjaxController extends Controller
{
    /**
     * @Route("/contracts", name="tenant_contracts")
     * @Template()
     */
    public function contractsAction()
    {
        return array();
    }

    /**
     * @Route(
     *  "/bureau/start",
     *  name="tenant_reporting_start",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function startBureauReporting()
    {
        $request = $this->getRequest();
        $data = $request->request->all('action');
        $action = $data['action'];
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
        return new JsonResponse(array());
    }

    /**
     * @Route(
     *  "/bureau/reporting",
     *  name="tenant_contract_reporting",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function contractBureauReporting()
    {
        $request = $this->getRequest();
        $data = $request->request->all('data');
        $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($data['contract_id']);
        $action = $data['action'];
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if ($action == 'start') {
            $contract->setReporting(true);
        } else {
            $contract->setReporting(false);
        }
        $em->persist($contract);
        $em->flush();
        return new JsonResponse(array($action));
    }
}
