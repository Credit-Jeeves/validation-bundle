<?php
/**
 * Created by PhpStorm.
 * User: tobur
 * Date: 8/22/14
 * Time: 5:53 PM
 */

namespace CreditJeeves\UserBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bridge\Monolog\Logger;

/**
 * @DI\Service("user.log")
 */
class LogUser
{
    /**
     * @var $logger Logger
     */
    protected $logger;

    /**
     * @var $request Request
     */
    protected $request;

    /**
     * @DI\InjectParams({
     *     "logger"      = @DI\Inject("logger"),
     *     "request"     = @DI\Inject("request_stack")
     * })
     */
    public function __construct(Logger $logger, RequestStack $request)
    {
        $this->request = $request->getCurrentRequest();
        $this->logger = $logger;
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
}
