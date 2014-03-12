<?php

namespace RentJeeves\LandlordBundle\Listener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Add data after serialization
 *
 * @Service("report.listener.serializationlistener")
 * @Tag("jms_serializer.event_subscriber")
 */
class SerializationListener implements EventSubscriberInterface
{

    /**
     * @Inject("report.order.export", required = true)
     */
    public $reportOrder;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event'  => 'serializer.pre_serialize',
                'class'  => 'CreditJeeves\DataBundle\Entity\Order',
                'method' => 'onPreSerializeOrder'
            ),
            array(
                'event'  => 'serializer.pre_serialize',
                'class'  => 'CreditJeeves\DataBundle\Entity\Operation',
                'method' => 'onPreSerializeOperation'
            ),
        );
    }

    public function onPreSerializeOrder(ObjectEvent $event)
    {
        $context = $event->getContext();
        $groups = $context->attributes->values();
        $format = $context->getFormat();

        if ($format != 'xml' || !in_array('xmlReport', $groups[0])) {
            return;
        }

        $order = $event->getObject();
        $order->setPropertyId($this->reportOrder->getPropertyId());
    }

    public function onPreSerializeOperation(ObjectEvent $event)
    {
        $context = $event->getContext();
        $groups = $context->attributes->values();
        $format = $context->getFormat();

        if ($format != 'xml' || !in_array('xmlReport', $groups[0])) {
            return;
        }

        $operation = $event->getObject();
        $operation->setPropertyId($this->reportOrder->getPropertyId());
        $operation->setAccountId($this->reportOrder->getAccountId());
        $operation->setArAccountId($this->reportOrder->getArAccountId());
    }
}
