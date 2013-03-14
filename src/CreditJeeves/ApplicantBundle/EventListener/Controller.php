<?php
namespace CreditJeeves\ApplicantBundle\EventListener;

use CreditJeeves\CoreBundle\EventListener\Controller as Base;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
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
            !$controller->getUser()->getScores()->last()
        ) {
            throw new HttpException(307, null, null, array('Location' => $controller->generateUrl('core_report_get')));
        }
        parent::onKernelController($event);
    }
}
