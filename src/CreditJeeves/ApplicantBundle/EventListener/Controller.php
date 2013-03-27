<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\EventListener\Controller as Base;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 * @Service("applicant.event_listener.controller", parent = "core.event_listener.controller")
 *
 * FIXME move to Base
 * @Tag("kernel.event_listener", attributes = { "event" = "kernel.controller", "method" = "onKernelController" })
 * @Tag("kernel.event_listener", attributes = { "event" = "kernel.response", "method" = "onKernelResponse" })
 */
class Controller extends Base
{
    /**
     * {@inheritdoc}
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        /** @var $controller \Symfony\Bundle\FrameworkBundle\Controller\Controller */
        $controller = $event->getController();
        $controller = $controller[0];
        if (strstr(__CLASS__, 'Bundle\\', true) == strstr(get_class($controller), 'Bundle\\', true) &&
            !$controller->getUser()->getReportsPrequal()->last()
        ) {
            $request = $controller->getRequest();
            $currentUrl = 'http';
            if ($request->server->get('HTTPS')) {
                $currentUrl .= 's';
            }
            $currentUrl .= '://' . $request->server->get('HTTP_HOST') . $request->getPathInfo();
            throw new HttpException(
                307,
                null,
                null,
                array(
                    'Location' => $controller->generateUrl('core_report_get'),
                    'Referer' => $currentUrl // TODO find variant which works
                )
            );
        }
        parent::onKernelController($event);
    }
}
