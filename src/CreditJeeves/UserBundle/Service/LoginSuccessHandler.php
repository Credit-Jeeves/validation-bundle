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
     * @var SecurityContext
     */
    protected $security;

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
                $url = $this->container->get('router')->generate('landlord_homepage');
                $this->inviteProcess($User);
                break;
        }
        if (!$isDefense = $this->checkLogindefense($User)) {
            $url = $this->container->get('router')->generate('fos_user_security_login');
        }
        return new RedirectResponse($url);
    }

    private function checkLogindefense($user)
    {
        $defense = $user->getDefense();
        if ($defense) {
            $now = new \DateTime('now');
            $attempts = $defense->getAttempts();
            $last = $defense->getUpdatedAt();
            $interval = $now->diff($last, true)->format('%i');
            if ($attempts > 5) {
                if ($interval < 30) {
                    $this->container->get('session')->getFlashBag()->add(
                        'defense',
                        sprintf('Please, try "%s" minutes later.', (30- $interval))
                    );
                    return false;
                } else {
                    $defense->setAttempts(0);
                    $em = $this->container->get('doctrine.orm.default_entity_manager');
                    $user->setDefense($defense);
                    $em->persist($user);
                    $em->flush();
                }
            } else {
                $defense->setAttempts(0);
                $em = $this->container->get('doctrine.orm.default_entity_manager');
                $user->setDefense($defense);
                $em->persist($user);
                $em->flush();
            }
        }
        return true;
    }

    private function inviteProcess($user)
    {

        if (!$user) {
            return false;
        }

        $request = $this->container->get('request');
        $session = $request->getSession();
        $inviteCode = $session->get('inviteCode');

        if (!$inviteCode) {
            return false;
        }

        $session->remove('inviteCode');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'invite_code' => $inviteCode,
                'is_active'   => 0,
            )
        );

        if (!$landlord) {
            return false;
        }

        $contractsLandlord = $em->getRepository('RjDataBundle:Contract')->getContractsLandlord($landlord);

        if (empty($contractsLandlord)) {
            return false;
        }

        $holding = $user->getHolding();
        $group = $user->getCurrentGroup();

        foreach ($contractsLandlord as $key => $contract) {

            if ($contract->getStatus() != ContractStatus::INVITE) {
                continue;
            }
            if ($holding) {
                $contract->setHolding($holding);
            }
            if ($group) {
                $contract->setGroup($group);
            }
            $contract->setStatus(ContractStatus::PENDING);
            $em->persist($contract);
        }

        $em->flush();
        $contractsLandlord = $em->getRepository('RjDataBundle:Contract')->getContractsLandlord($landlord);

        if (!empty($contractsLandlord)) {
            return true;
        }

        $em->remove($landlord);
        $em->flush();

        return true;
    }
}
