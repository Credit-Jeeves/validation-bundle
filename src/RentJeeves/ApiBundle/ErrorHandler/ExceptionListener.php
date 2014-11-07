<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("api.events")
 * @DI\Tag("kernel.event_listener",
 *     attributes = {
 *         "event" = "kernel.exception",
 *         "method" = "onKernelException"
 *     }
 * )
 */
class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var ApiExceptionWrapperHandler
     * @DI\Inject("fos_rest.view.exception_wrapper_handler")
     */
    public $handler;

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $handling = true;

        $path = $event->getRequest()->getRequestUri();

        if (strpos($path, '/api/') !== 0) {
            return;
        }

        $event->setResponse($this->handler->handle($event->getException(), $event->getRequest()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 500],
        ];
    }
}
