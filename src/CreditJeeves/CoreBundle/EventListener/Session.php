<?php

namespace CreditJeeves\CoreBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpKernel\KernelEvents;
use JMS\DiExtraBundle\Annotation\Tag;
use \DateTime;
use \DateTimeZone;

/**
 * Class ResponseListener
 * @package CreditJeeves\CoreBundle\Event
 *
 * @Service
 * @Tag(name="kernel.event_listener", attributes = {"event"=KernelEvents::RESPONSE })
 */
class Session
{


    /**
     * @var int
     */
    private static $call = 0;

    /**
     * @Inject("session", required = true)
     */
    public $session;

    /**
     * @Inject("security.context", required = true)
     */
    public $security;

    /**
     * @Inject("%session.lifetime%", required = true)
     */
    public $sessionLifeTime;

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return;
        }

        if (self::$call >= 1) {
            return;
        }

        $response = $event->getResponse();
        $date = new DateTime('now', new DateTimeZone('GMT'));

        $date->modify('+'.$this->sessionLifeTime.' seconds');

        $nameSession = $this->session->getName();
        $name = $nameSession.'_expiration_date';
        $cookies = $response->headers->getCookies();
        $response->headers->setCookie(
            new Cookie(
                $name,
                $date->modify('-5 seconds')->format('r'),
                $expire = 0,
                $path = null,
                $domain = null,
                $secure = false,
                $httpOnly = false
            )
        );

        self::$call++;
    }
}
