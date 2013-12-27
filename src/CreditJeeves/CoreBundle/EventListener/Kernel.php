<?php
namespace CreditJeeves\CoreBundle\EventListener;

use CreditJeeves\CoreBundle\Event\Filter;
use CreditJeeves\DataBundle\Entity\Client;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\DiExtraBundle\Annotation\Inject;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("core.event_listener.kernel")
 *
 * @Tag("kernel.event_listener", attributes = { "event" = "kernel.request", "method" = "request" })
 * @Tag("kernel.event_listener", attributes = { "event" = "kernel.request", "method" = "processApi" })
 */
class Kernel
{
    const API_CONTROLLER = 'CreditJeeves\ApiBundle\Controller\TokenController::tokenAction';

    protected $em;

    /**
     * @InjectParams({
     *     "em"           = @Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function request(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        if (preg_match('/.*Jeeves\\\\(.*)Bundle\\\\Controller/i', $controller, $matches)) {
            $eventName = strtolower($matches[1]) . '.filter';
            $dispatcher = $event->getDispatcher();
            $newEvent = new Filter();
            $newEvent->setDispatcher($dispatcher);
            $newEvent->setName($eventName);
            $newEvent->setResponseEvent($event);

            $dispatcher->dispatch($eventName, $newEvent);
        }
    }


    public function processApi(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getRequest()->attributes->get('_controller');

        if ($controller !== self::API_CONTROLLER) {
            return;
        }

        $clients = $this->em->getRepository('DataBundle:Client')->findAll();

        if (!$clients || !isset($clients[0])) {
            throw new HttpException('API clients are empty. Please, configure them.');
        }

        if (count($clients) != 1) {
            throw new HttpException('There are more than one API client. Please, configure it correctly.');
        }

        /**
         * @var Client $client
         */
        $client = reset($clients);
        // Setup client id and secret id
        if ($request->getMethod() === 'POST') {
            $request->request->set('client_id', $client->getPublicId());
            $request->request->set('client_secret', $client->getSecret());
            return;
        }

        $request->query->set('client_id', $client->getPublicId());
        $request->query->set('client_secret', $client->getSecret());
    }
}
