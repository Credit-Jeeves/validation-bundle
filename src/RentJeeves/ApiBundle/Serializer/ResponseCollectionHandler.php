<?php

namespace RentJeeves\ApiBundle\Serializer;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\ArrayCollectionHandler;

class ResponseCollectionHandler extends ArrayCollectionHandler
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
}
