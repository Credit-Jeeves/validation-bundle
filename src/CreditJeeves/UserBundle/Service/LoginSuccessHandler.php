<?php
namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @DI\Service("user.service.login_success_handler")
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $User = $token->getUser();
        $sType = $User->getType();
        switch ($sType) {
            case UserType::APPLICANT:
                $this->container->get('core.session.applicant')->setUser($User);
                $url = $this->container->get('router')->generate($sType . '_homepage');
                break;
            case UserType::DEALER:
                $url = $this->container->get('router')->generate('fos_user_security_login');
                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    'Please log in using the Dealership link to the right.'
                );
                break;
            case UserType::ADMIN:
                $this->container->get('core.session.admin')->setUser($User);
                $url = $this->container->get('router')->generate('sonata_admin_dashboard');
                break;
            case UserType::TETNANT:
                $this->container->get('core.session.tenant')->setUser($User);
                $url = $this->container->get('router')->generate('tenant_homepage');
                break;
            case UserType::LANDLORD:
                $this->container->get('core.session.landlord')->setUser($User);
                $url = $this->generateLanlordUrl($User);
                break;
        }
        /**
         * @var $userLogAndDefence LogAndDefenceUser
         */
        $userLogAndDefence = $this->container->get('user.log_and_defence');
        $userLogAndDefence->signin($User->getEmail(), $status = 'success');

        if ($userLogAndDefence->isDefense($User)) {
            $url = $userLogAndDefence->getRedirectLinkForDefense();
        } else {
            $userLogAndDefence->clearDefense($User);
        }

        return new RedirectResponse($url);
    }

    private function generateLanlordUrl($user)
    {
        $url = $this->container->get('router')->generate('landlord_homepage');
        $contracts = $user->getHolding()->getContracts();
        if (count($contracts) == 1  && ContractStatus::PENDING == $contracts->last()->getStatus()) {
            $url = $this->container->get('router')->generate('landlord_tenants');
        }
        return $url;
    }
}
