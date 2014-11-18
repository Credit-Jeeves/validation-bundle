<?php

namespace RentJeeves\ApiBundle\Serializer;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use PhpOption\Some as Option;

/**
 * Add data after serialization
 *
 * @Service("api.listener.serialization_listener")
 * @Tag("jms_serializer.event_subscriber")
 */
class SerializationListener implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'RentJeeves\DataBundle\Entity\PaymentAccount',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'Proxies\__CG__\RentJeeves\DataBundle\Entity\PaymentAccount',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'RentJeeves\DataBundle\Entity\Unit',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'Proxies\__CG__\RentJeeves\DataBundle\Entity\Unit',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'RentJeeves\DataBundle\Entity\Payment',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'Proxies\__CG__\RentJeeves\DataBundle\Entity\Payment',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'CreditJeeves\DataBundle\Entity\Order',
                'method' => 'onPreSerialize'
            ],
            [
                'event' => 'serializer.pre_serialize',
                'class' => 'Proxies\__CG__\CreditJeeves\DataBundle\Entity\Order',
                'method' => 'onPreSerialize'
            ],
        ];
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        $useWrapper = $event->getContext()->attributes->get('use_wrapper');
        $useWrapper = ($useWrapper instanceof Option) ? $useWrapper->get() : false;
        if ($useWrapper) {
            $event->setType('NeedWrapped');
        }
    }
}
