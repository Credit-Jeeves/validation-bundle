<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Validators\TenantEmailValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\TenantType;
use RentJeeves\PublicBundle\Form\ReturnedType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $tenant  = $this->getDoctrine()->getRepository('RjDataBundle:Tenant')->findOneBy(array('invite_code' => $code));

        if (empty($tenant)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $form = $this->createForm(
            new TenantType(),
            $tenant,
            array('inviteEmail' => false)
        );
        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $form->getData();
            $aForm = $request->request->get($form->getName());
            $password = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($aForm['password']['Password'], $tenant->getSalt());
            $tenant->setPassword($password);
            $tenant->setCulture($this->container->parameters['kernel.default_locale']);
            $em = $this->getDoctrine()->getManager();
            $tenant->setInviteCode(null);
            $em->persist($tenant);
            $em->flush();

            return $this->login($tenant);
        }
        return array(
            'code'              => $code,
            'form'              => $form->createView(),
        );
    }

    private function login($tenant)
    {
        $response = new RedirectResponse($this->generateUrl('tenant_homepage'));
        $this->container->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $tenant,
            $response
        );

        $this->container->get('user.service.login_success_handler')
                ->onAuthenticationSuccess(
                    $this->container->get('request'),
                    $this->container->get('security.context')->getToken()
                );

        return $response;
    }

    /**
     * @Route("/rj_returned", name="tenant_returned")
     * @Template()
     *
     * @return array
     */
    public function returnedAction()
    {
        
        $tenant = $this->getUser();
        $form = $this->createForm(
            new ReturnedType(),
            $tenant
        );
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);
                $em = $this->getDoctrine()->getManager();
                $tenant->setInviteCode(null);
                $tenant->setHasData(true);
                $em->persist($tenant);
                $em->flush();
                return $this->login($tenant);
            }
        }
        return array(
            'form' => $form->createView(),
        );
    }
}
