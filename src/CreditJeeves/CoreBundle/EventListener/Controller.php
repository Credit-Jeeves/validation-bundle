<?php
namespace CreditJeeves\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 *
 * @Service("core.event_listener.controller", abstract=true)
 *
 * FIXME implement chain calls as it was in SF1
 */
abstract class Controller
{
    /**
     * Executes before Controller
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
    }

    /**
     * Executes after Controller
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
    }
}
