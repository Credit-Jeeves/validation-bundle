<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\LoginDefense;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("user.service.login_defense")
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

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "em"     = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(ContainerInterface $container, $em)
    {
        $this->container = $container;
        $this->em = $em;
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
        if ($user) {
            $this->container->get('session')->getFlashBag()->add(
                'error',
                $exception->getMessage()
                // Before we have always 'Incorrect email or password.' lets show correct message
            );
            $defense = $user->getDefense();
            if ($defense) {
                $defense->addAttempt();
            } else {
                $defense = new LoginDefense();
                //$user->setDefense($defense);
                $defense->setUser($user);
            }
            $this->em->persist($defense);
            $this->em->flush();
        } else {
            $this->container->get('session')->getFlashBag()->add(
                'error',
                $this->container->get('translator')->trans('login.error.msg')
            );
        }
        $url = $this->container->get('router')->generate('fos_user_security_login');
        return new RedirectResponse($url);
    }
}
