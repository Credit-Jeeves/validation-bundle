<?php

namespace RentJeeves\CoreBundle\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\DateHandler as BaseDateHandler;

class DateHandler extends BaseDateHandler
{
    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $types = ['DateTime', 'DateInterval'];

        foreach ($types as $type) {
            $methods[] = array(
                'type' => $type,
                'format' => 'array',
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'method' => 'serialize'.$type,
            );
        }

        return $methods;
    }
}
