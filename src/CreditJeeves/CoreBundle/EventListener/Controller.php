<?php
namespace CreditJeeves\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class Controller
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
