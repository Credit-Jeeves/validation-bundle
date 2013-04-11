<?php
namespace CreditJeeves\CoreBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("core.event_listener.kernel")
 *
 * @Tag("kernel.event_listener", attributes = { "event" = "kernel.request", "method" = "request" })
 */
class Kernel
{
    public function request(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        if (preg_match('/CreditJeeves\\\\(.*)Bundle\\\\Controller/i', $controller, $matches)) {
            $eventName = strtolower($matches[1]) . '.filter';
            $dispatcher = $event->getDispatcher();
            $newEvent = new Filter();
            $newEvent->setDispatcher($dispatcher);
            $newEvent->setName($eventName);
            $newEvent->setResponseEvent($event);

            $dispatcher->dispatch($eventName, $newEvent);

        }
    }
}
