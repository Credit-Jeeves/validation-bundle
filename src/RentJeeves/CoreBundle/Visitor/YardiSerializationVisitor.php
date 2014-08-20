<?php

namespace RentJeeves\CoreBundle\Visitor;

use JMS\Serializer\Context;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("jms_serializer.yardi_serialization_visitor")
 * @DI\Tag("jms_serializer.serialization_visitor", attributes = {"format" = "yardi"})
 */
class YardiSerializationVisitor extends XmlSerializationVisitor
{
    /**
     * @DI\InjectParams({
     *     "namingStrategy" = @DI\Inject("jms_serializer.naming_strategy")
     * })
     */
    public function __construct(PropertyNamingStrategyInterface $namingStrategy)
    {
        parent::__construct($namingStrategy);
    }

    public function visitProperty(PropertyMetadata $metadata, $object, Context $context)
    {
        $exclusionStrategy = $context->getExclusionStrategy();

        if (null !== $exclusionStrategy && $exclusionStrategy->shouldSkipProperty($metadata, $context, $object)) {
            return;
        }

        parent::visitProperty($metadata, $object, $context);
    }


}
