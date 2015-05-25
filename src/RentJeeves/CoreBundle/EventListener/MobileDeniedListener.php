<?php

namespace RentJeeves\CoreBundle\EventListener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Service("core.event_listener.kernel.mobile_denied")
 *
 *  @Tag(
 *      "kernel.event_listener",
 *       attributes = {
 *           "event" = "kernel.request",
 *           "method" = "onKernelRequest",
 *      }
 * )
 */
class MobileDeniedListener
{

    const SKIP_CONTROLLER_REG_EXP = "/flashAction|getTranslationsAction|PublicBundle/";

    protected $router;

    protected $session;

    protected $translator;

    /**
     * @InjectParams({
     *      "router"                 = @Inject("router"),
     *      "session"                = @Inject("session"),
     *      "translator"             = @Inject("translator")
     * })
     */
    public function __construct(
        Router $router,
        Session $session,
        $translator
    ) {
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');

        if (($this->isMobile() && !preg_match(self::SKIP_CONTROLLER_REG_EXP, $controller))
            && ($event->getRequestType() === 1)
        ) {
            $title = $this->session->getFlashBag()->set(
                'message_title',
                $this->translator->trans('access.denied')
            );
            $text = $this->session->getFlashBag()->set(
                'message_body',
                $this->translator->trans('access.denied.description')
            );
            $route = $this->router->generate('public_message_flash');
            $event->setResponse(new RedirectResponse($route));
        }
    }

    /**
     * @deprecated
     * @return bool
     */
    protected function isMobile()
    {
//        //On task https://credit.atlassian.net/browse/RT-276
//        //was changed, currently we need allow ipad/mobile
//        return false;
//
//        $userAgent = (isset($_SERVER['HTTP_USER_AGENT']))? $_SERVER['HTTP_USER_AGENT'] : null;
//
//        if (!$userAgent) {
//            return false;
//        }
//
//        $preg = "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|";
//        $preg .= "windows ce|nokia|fennec|hiptop|kindle|mot |mot-|IEMobile|Android|";
//        $preg .= "webos\/|samsung|sonyericsson|^sie-|nintendo|";
//        $preg .= "mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /";
//
//        if (preg_match($preg, $userAgent)) {
//            return true;
//        }
        return false;
    }
}
