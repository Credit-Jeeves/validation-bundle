<?php
namespace RentJeeves\CoreBundle\Visitor;

use JMS\Serializer\GenericSerializationVisitor;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("jms_serializer.array_serialization_visitor")
 * @DI\Tag("jms_serializer.serialization_visitor", attributes = {"format" = "array"})
 */
class ArraySerializationVisitor extends GenericSerializationVisitor
{
    /**
     * @DI\InjectParams({
     *     "namingStrategy" = @DI\Inject("jms_serializer.naming_strategy")
     * })
     */
    public function __construct($namingStrategy)
    {
        parent::__construct($namingStrategy);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        return $this->getRoot();
    }
}
