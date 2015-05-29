<?php

namespace RentJeeves\OAuthServerBundle\Controller;

use CreditJeeves\DataBundle\Entity\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

class SecurityController extends BaseController
{
    /**
     * @Route("/oauth/v2/auth_login", name="oauth_server_login")
     * @Template("OAuthServerBundle:Security:login.html.twig")
     *
     * @return array
     */
    public function loginAction()
    {
        return parent::loginAction();
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        $template = sprintf(
            'OAuthServerBundle:Security:login.html.%s',
            $this->container->getParameter('fos_user.template.engine')
        );

        $request = $this->container->get('request');
        /* @var Request $request */
        $session = $request->getSession();
        /* @var Session $session */
        $clientId = $session->get('client_id');

        if ($clientId) {
            /** @var Client $client */
            $client = $this->container
                ->get('fos_oauth_server.client_manager')
                ->findClientByPublicId($clientId);
            if ($client) {
                $data['clientName'] = $client->getName();
            }
        }
        isset($data['clientName']) || $data['clientName'] ='';

        return $this->container->get('templating')->renderResponse($template, $data);
    }

    /**
     * @param Request $request
     *
     * @Route("/oauth/v2/auth_login_check", name="oauth_server_login_check")
     */
    public function loginCheckAction(Request $request)
    {

    }

    /**
     * @Route("/oauth/v2/auth_logout", name="oauth_server_logout")
     */
    public function logoutAction()
    {
        parent::logoutAction();
    }
}
