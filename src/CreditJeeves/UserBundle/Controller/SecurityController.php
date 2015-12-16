<?php

namespace CreditJeeves\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class SecurityController extends BaseController
{

    /**
     * @Route("/login", name="fos_user_security_login")
     * @Route("/invite/login/{inviteCode}", name="fos_user_security_login_invite")
     * @Route("/login/defence/{defenseMessage}", name="fos_user_security_login_defence")
     */
    public function loginAction($inviteCode = null, $defenseMessage = null)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        $routeName = $request->get('_route');

        if (!empty($inviteCode) && $routeName === 'fos_user_security_login_invite') {
            $session->set('inviteCode', $inviteCode);
        }

        if ($routeName === 'fos_user_security_login_defence') {
            $this->container->get('security.context')->setToken(
                new AnonymousToken('main', 'anon.')
            ); //do logout user
        }

        return parent::loginAction();
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    protected function renderLogin(array $data)
    {
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $logger = $this->container->get('logger');
            $logger->debug("new controller user agent: " . $userAgent);
            $commonPhones="/phone|iphone|itouch|ipod|symbian|android|htc_|htc-";
            $commonOrganizersAndBrowsers="|palmos|blackberry|opera mini|iemobile|windows ce|";
            $uncommonDevices="nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/";
            if (preg_match($commonPhones.$commonOrganizersAndBrowsers.$uncommonDevices, $userAgent)) {
                $template =
                    sprintf(
                        'FOSUserBundle:Security:login.mobile.html.%s',
                        $this->container->getParameter('fos_user.template.engine')
                    );
            }
        }
        if (!isset($template)) {
            $template =
                sprintf(
                    'FOSUserBundle:Security:login.html.%s',
                    $this->container->getParameter('fos_user.template.engine')
                );
        }

        $data['loginMessage'] = $this->getLoginMessage();

        return $this->container->get('templating')->renderResponse($template, $data);
    }

    /**
     * @return string|null
     */
    protected function getLoginMessage()
    {
        if ($settings = $this->container->get('doctrine')->getRepository('DataBundle:Settings')->findAll()) {
            $settings = $settings[0];

            return $settings->getLoginMessage();
        }

        return null;
    }
}
