<?php

namespace RentJeeves\OAuthServerBundle\Controller;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\ContractWaitingRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\TenantRepository;
use RentJeeves\OAuthServerBundle\Form\TenantType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class PublicController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/oauth/v2/auth_reg", name="fos_oauth_server_registration")
     * @Template()
     *
     * @return array
     */
    public function registrationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tenant = $this->getTenant();
        $form = $this->createForm(
            $tenantType = new TenantType(),
            $tenant,
            $tenant->getId() ? ['inviteEmail' => false] : []
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $tenant Tenant */
            $tenant = $form->getData();
            $password = $form->get('password')->getData();
            $password = $this->container->get('user.security.encoder.digest')
                ->encodePassword($password, $tenant->getSalt());

            $tenant->setPassword($password);
            $tenant->setCulture($this->container->parameters['kernel.default_locale']);

            $em->persist($tenant);
            $em->flush();

            $this->get('project.mailer')->sendRjCheckEmail($tenant);

            /** @var $contractWaiting ContractWaiting */
            if ($contractWaitingId = $request->getSession()->get('contract_waiting_id') and
                $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaitingId)
            ) {
                /** @var ContractProcess $contractProcessor */
                $contractProcessor = $this->container->get('contract.process');
                $contract = $contractProcessor->createContractFromWaiting($tenant, $contractWaiting);

                if (!$contract) {
                    $this->container->get('logger')->alert(
                        '[OAuth Registration]Can not create new contract from contractWaiting #' . $contractWaitingId,
                        $contractProcessor->getErrors()
                    );
                }

                $request->getSession()->remove('contract_waiting_id');
            }

            $request->getSession()->remove('holding_id');
            $request->getSession()->remove('resident_id');

            $targetUrl = $request->getSession()->get('_security.oauth_authorize.target_path');

            $targetUrl || $targetUrl = $this->generateUrl('tenant_homepage');

            return $this->get('common.login.manager')->loginAndRedirect(
                $tenant,
                $targetUrl
            );
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @return Tenant
     */
    protected function getTenant()
    {
        /** @var Session $session */
        $session = $this->container->get('session');
        $em = $this->getDoctrine()->getManager();

        $holdingId = $session->get('holding_id');
        $residentId = $session->get('resident_id');

        if ($holdingId && $residentId) {
            // Try to find tenant by resident_id.
            /** @var Holding $holding */
            $holding = $em->getRepository('DataBundle:Holding')->find($holdingId);
            if (!$holding) {
                $session->remove('holding_id');
                $session->remove('resident_id');

                return new Response('Holding not found', Response::HTTP_BAD_REQUEST);
            }
            /** @var  TenantRepository $repo */
            $repo = $em->getRepository('RjDataBundle:Tenant');

            $tenant = $repo->getTenantWithPendingInvitationByHoldingAndResidentId($holding, $residentId);

            if ($tenant) {
                return $tenant;
            }

            // Try to find waiting contract by resident_id.
            /** @var ContractWaitingRepository $repo */
            $repo = $em->getRepository('RjDataBundle:ContractWaiting');
            $contractsWaiting = $repo->findAllByHoldingAndResidentId($holding, $residentId, $sortReverse = true);

            if (count($contractsWaiting) > 0) {
                $contractWaiting = $contractsWaiting[0];

                $tenant = new Tenant();
                $tenant->setFirstName($contractWaiting->getFirstName());
                $tenant->setLastName($contractWaiting->getLastName());

                $session->set('contract_waiting_id', $contractWaiting->getId());

                return $tenant;
            }
        }

        return new Tenant();
    }
}
