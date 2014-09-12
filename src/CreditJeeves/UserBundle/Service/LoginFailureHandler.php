<?php
namespace CreditJeeves\UserBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("user.service.login_failure_handler")
 */
class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Entity Manager
     */
    protected $em;

    protected $exceptionCatcher;

    /**
     * @DI\InjectParams({
     *     "container"              = @DI\Inject("service_container"),
     *     "em"                     = @DI\Inject("doctrine.orm.entity_manager"),
     *     "exceptionCatcher"       = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(ContainerInterface $container, $em, $exceptionCatcher)
    {
        $this->container = $container;
        $this->em = $em;
        $this->exceptionCatcher = $exceptionCatcher;
    }

    /**
     * 
     * @param Request $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = $request->request->All('_username');
        $username = $data['_username'];
        $user = $this->em->getRepository('DataBundle:User')->findOneByEmail($username);
        /**
         * @var $userLogAndDefence LogAndDefenceUser
         */
        $userLogAndDefence = $this->container->get('user.log_and_defence');
        $userLogAndDefence->signin($username, $status = 'failure');

        if ($user && $userLogAndDefence->isDefense($user)) {
            $url = $userLogAndDefence->getRedirectLinkForDefense();
        } else {
            $this->container->get('session')->getFlashBag()->add(
                'error',
                $this->container->get('translator')->trans('login.error.msg')
            );
            $url = $this->container->get('router')->generate('fos_user_security_login');
        }
        //$this->exceptionCatcher->handleException($exception);

        $redirect = new RedirectResponse($url);
        $redirect->send();
        return $redirect;
    }
}
