<?php

namespace RentJeeves\CoreBundle\Visitor;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\scalar;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\Util\Writer;

/**
 * @Service("jms_serializer.csv_serialization_visitor")
 * @Tag("jms_serializer.serialization_visitor", attributes = {"format" = "csv"})
 */
class CsvSerializationVisitor  extends AbstractVisitor implements VisitorInterface
{
    protected $writer;

    public function __construct()
    {
        echo "Construct\n";
        $this->writer = new Writer();
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitNull($data, array $type, Context $context)
    {
        var_dump($data);
        var_dump($type);
        echo "visitNull\n";
        // TODO: Implement visitNull() method.
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitString($data, array $type, Context $context)
    {
        // TODO: Implement visitString() method.
        echo "visitString\n";
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type, Context $context)
    {
        echo "visitBoolean\n";
        // TODO: Implement visitBoolean() method.
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitDouble($data, array $type, Context $context)
    {
        echo "visitDouble\n";
        // TODO: Implement visitDouble() method.
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitInteger($data, array $type, Context $context)
    {
        echo "VisitInteger\n";
        // TODO: Implement visitInteger() method.
    }

    /**
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function visitArray($data, array $type, Context $context)
    {
        echo "visitArray()\n";
        var_dump($data);
        // TODO: Implement visitArray() method.
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     *
     * @return void
     */
    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        // TODO: Implement startVisitingObject() method.
        echo "startVisitingObject\n";
    }

    /**
     * @param PropertyMetadata $metadata
     * @param mixed $data
     *
     * @return void
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        echo "visitProperty\n";
        // TODO: Implement visitProperty() method.
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     *
     * @return mixed
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        echo "endVisitingObject\n";
        // TODO: Implement endVisitingObject() method.
    }

    /**
     * Called before serialization/deserialization starts.
     *
     * @param GraphNavigator $navigator
     *
     * @return void
     */
    public function setNavigator(GraphNavigator $navigator)
    {
        echo "setNavigator\n";
        // TODO: Implement setNavigator() method.
    }

    /**
     * @deprecated use Context::getNavigator/Context::accept instead
     * @return GraphNavigator
     */
    public function getNavigator()
    {
        echo "getNavigator\n";
        // TODO: Implement getNavigator() method.
    }

    /**
     * @return object|array|scalar
     */
    public function getResult()
    {
        echo "getResult\n";
        // TODO: Implement getResult() method.
    }

}