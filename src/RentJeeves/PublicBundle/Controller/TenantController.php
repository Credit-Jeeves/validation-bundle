<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\TenantType;
use CreditJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;

class TenantController extends Controller
{
    /**
     * @Route("/tenant/invite/{code}", name="tenant_invite")
     * @Template()
     *
     * @return array
     */
    public function tenantInviteAction($code)
    {
        $tenant  = $this->getDoctrine()->getRepository('DataBundle:Tenant')->findOneBy(array('invite_code' => $code));

        if (empty($tenant)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $form = $this->createForm(
            new TenantType(),
            $tenant
        );
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData();
                $aForm = $request->request->get($form->getName());
                $tenant->setPassword(md5($aForm['password']['Password']));
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);

                $contracts = $tenant->getContracts();
                $em = $this->getDoctrine()->getManager();
                if (!empty($contracts)) {
                    foreach ($contracts as $contract) {
                        if ($contract->getStatus() == ContractStatus::INVITE) {
                            $contract->setStatus(ContractStatus::PENDING);
                            $em->persist($contract);
                        }
                    }
                }

                $em->persist($tenant);
                $em->flush();

                $this->get('creditjeeves.mailer')->sendRjCheckEmail($tenant);

                return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$tenant->getId())));
            }
        }

        return array(
            'code'      => $code,
            'form'      => $form->createView(),
        );
    }
}
