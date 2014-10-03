<?php

namespace RentJeeves\ApiBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\ApiBundle\ResponseEntity\Unit as UnitWrapper;

class WrapperHandler implements SubscribingHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'NeedWrapped',
                'method' => 'wrapUnit',
            ]
        ];
    }


    public function wrapUnit(
        JsonSerializationVisitor $visitor,
        Unit $unit,
        array $type,
        Context $context
    ) {
        $unit = new UnitWrapper($unit);
        $type['name'] = get_class($unit);

        return $visitor->getNavigator()->accept($unit, $type, $context);
    }
}
