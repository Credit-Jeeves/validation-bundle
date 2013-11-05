<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\PublicBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LoginType;
use RentJeeves\DataBundle\Entity\Tenant;
// use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
// use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
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
                $isValid = $this->get('user.security.encoder.digest')->isPasswordValid($user->getPassword(), $tenant->getPassword(), $user->getSalt());
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
}
