<?php

namespace CreditJeeves\UserBundle\Service;

use CreditJeeves\DataBundle\Entity\LoginDefense;
use CreditJeeves\DataBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Monolog\Logger;

/**
 * @DI\Service("user.log_and_defence")
 */
class LogAndDefenceUser
{
    /**
     * @var $logger Logger
     */
    protected $logger;

    /**
     * @var $request Request
     */
    protected $request;

    protected $em;

    protected $translator;

    protected $router;

    protected $loginDelay;

    protected $loginAttempts;

    /**
     * @DI\InjectParams({
     *     "logger"         = @DI\Inject("logger"),
     *     "request"        = @DI\Inject("request_stack"),
     *     "em"             = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "router"         = @DI\Inject("router"),
     *     "loginDelay"     = @DI\Inject("%login.delay%"),
     *     "loginAttempts"  = @DI\Inject("%login.attempts%"),
     * })
     */
    public function __construct(
        Logger $logger,
        RequestStack $request,
        $em,
        $translator,
        $router,
        $loginDelay,
        $loginAttempts
    ) {
        $this->request = $request->getCurrentRequest();
        $this->logger = $logger;
        $this->em = $em;
        $this->translator = $translator;
        $this->router = $router;
        $this->loginAttempts = $loginAttempts;
        $this->loginDelay = $loginDelay;
    }

    public function signin($login, $status)
    {
        $ips = implode(",", $this->request->getClientIps());
        $message = sprintf(
            'Signin %s, status: %s, IP: %s',
            $login,
            $status,
            $ips
        );

        $this->logger->info($message);
    }

    public function getMessageDefence()
    {
        return $this->messageDefence;
    }


    public function isDefense(User $user)
    {
        $defense = $user->getDefense();
        if (empty($defense)) {
            $defense = new LoginDefense();
            $defense->setUser($user);
        }

        $now = new \DateTime('now');
        $attempts = $defense->getAttempts();
        $last = $defense->getUpdatedAt();
        $interval = $now->diff($last, true)->format('%i');
        $isDefense = false;
        if ($attempts > $this->loginAttempts && $interval < $this->loginDelay) {
            $newDelay = $this->loginDelay - $interval;
            $this->messageDefence = $this->translator->trans(
                'login.defence',
                array(
                    '%MINUTES_LEFT%' => $newDelay
                )
            );

            $isDefense = true;
        }

        $defense->setAttempts($defense->getAttempts()+1);
        $this->em->persist($defense);
        $this->em->flush();

        return $isDefense;
    }

    public function getRedirectLinkForDefense()
    {
        return $this->router->generate(
            'fos_user_security_login_defence',
            array(
                'defenseMessage' => $this->getMessageDefence()
            )
        );
    }

    public function clearDefense(User $user)
    {
        $defense = $user->getDefense();
        if (!$defense) {
            return false;
        }
        $this->em->remove($defense);
        $this->em->flush();
        return true;
    }
}
