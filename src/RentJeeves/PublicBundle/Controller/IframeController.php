<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\PublicBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\PublicBundle\Form\LoginType;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IframeController extends Controller
{
    /**
     * @Route("/management", name="management_login")
     * @Template()
     */
    public function indexAction()
    {
        $tenant = new Tenant();
        $form = $this->createForm(
            new LoginType(),
            $tenant
        );
        $url = '';
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData();
                $user = $this->get('user.user_provider')->loadUserByUsername($tenant->getEmail());
                $isValid = $this->get('user.security.encoder.digest')->isPasswordValid(
                    $user->getPassword(),
                    $tenant->getPassword(),
                    $user->getSalt()
                );
                if ($isValid) {
                    $this->login($user);
                    $url = $this->generateUrl('tenant_homepage');
                }
            }
        }
        return array(
            'form' => $form->createView(),
            'url' => $url,
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
     * @Route(
     *     "/management/login",
     *     name="management_ajax_login",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function ajaxLoginAction()
    {
        $request = $this->get('request');
        $tenant = new Tenant();
        $form = $this->createForm(
            new LoginType(),
            $tenant
        );
        $url = '';
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $tenant = $form->getData();
                $user = $this->get('user.user_provider')->loadUserByUsername($tenant->getEmail());
                $isValid = $this->get('user.security.encoder.digest')->isPasswordValid(
                    $user->getPassword(),
                    $tenant->getPassword(),
                    $user->getSalt()
                );
                if ($isValid) {
                    $this->login($user);
                    $url = $this->generateUrl('tenant_homepage', array(), true);
                }
            }
        }
        return new JsonResponse(array('url' => $url));
    }
}
