<?php

namespace RentJeeves\OAuthServerBundle\Controller;

use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\OAuthServerBundle\Form\TenantType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $tenant = new Tenant();
        $form = $this->createForm(
            $tenantType = new TenantType(),
            $tenant
        );
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $password = $form->get('password')->getData();
            /**
             * @var $tenant Tenant
             */
            $tenant = $form->getData();
            $password = $this->container->get('user.security.encoder.digest')
                ->encodePassword($password, $tenant->getSalt());
            $tenant->setPassword($password);
            $tenant->setCulture($this->container->parameters['kernel.default_locale']);

            $em->persist($tenant);
            $em->flush();

            $this->get('project.mailer')->sendRjCheckEmail($tenant);
            return $this->get('common.login.manager')->loginAndRedirect(
                $tenant,
                $this->generateUrl('fos_oauth_server_authorize')
            );
        }
        return [
            'form' => $form->createView(),
        ];
    }
}
