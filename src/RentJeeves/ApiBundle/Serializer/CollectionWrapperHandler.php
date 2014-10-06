<?php

namespace RentJeeves\ApiBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\VisitorInterface;
use Doctrine\Common\Collections\Collection;

class CollectionWrapperHandler extends ArrayCollectionHandler
{
    public static function getSubscribingMethods()
    {
        $methods = parent::getSubscribingMethods();

        $type = 'RentJeeves\ApiBundle\ResponseEntity\ResponseCollection';
        $format = 'json';

        $methods[] = array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'type' => $type,
            'format' => $format,
            'method' => 'serializeCollection',
        );

        return $methods;
    }

    public function serializeCollection(
        VisitorInterface $visitor,
        Collection $collection,
        array $type,
        Context $context
    ) {
        $context->attributes->set('use_wrapper', true);

        return parent::serializeCollection($visitor, $collection, $type, $context);
    }
}
