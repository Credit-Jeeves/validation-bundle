<?php

namespace RentJeeves\ApiBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use RentJeeves\ApiBundle\Response\ResponseFactory;

class WrapperHandler implements SubscribingHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

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
                'method' => 'wrap',
            ]
        ];
    }


    public function wrap(
        JsonSerializationVisitor $visitor,
        $object,
        array $type,
        Context $context
    ) {
        $object = $this->responseFactory->getResponse($object);
        $type['name'] = get_class($object);

        return $visitor->getNavigator()->accept($object, $type, $context);
    }
}
