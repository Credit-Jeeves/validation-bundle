<?php

namespace RentJeeves\LandlordBundle\Listener;

use CreditJeeves\DataBundle\Entity\Operation;
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
     * @Inject("accounting.export.yardi", required = true)
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
                'class'  => 'CreditJeeves\DataBundle\Entity\Operation',
                'method' => 'onPreSerializeOperation'
            ),
        );
    }

    public function onPreSerializeOperation(ObjectEvent $event)
    {
        $context = $event->getContext();
        $groups = $context->attributes->values();
        $format = $context->getFormat();

        if ($format != 'yardi' || !in_array('xmlReport', $groups[0])) {
            return;
        }
        /**
         * @var $operation Operation
         */
        $operation = $event->getObject();
        $operation->initDetails(
            $this->reportOrder->getPropertyId(),
            $this->reportOrder->getAccountId(),
            $this->reportOrder->getArAccountId()
        );
        $operation->setPropertyId($this->reportOrder->getPropertyId());
    }
}
