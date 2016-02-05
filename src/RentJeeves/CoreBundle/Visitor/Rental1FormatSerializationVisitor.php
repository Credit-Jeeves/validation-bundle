<?php

namespace RentJeeves\CoreBundle\Visitor;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use RuntimeException;

/**
 * @Service("jms_serializer.rental_1_serialization_visitor")
 * @Tag("jms_serializer.serialization_visitor", attributes = {"format" = "rental_1"})
 */
class Rental1FormatSerializationVisitor extends AbstractVisitor
{
    private $content;
    private $navigator;

    public function __construct()
    {
        $this->content = '';
    }

    public function visitNull($data, array $type, Context $context)
    {
        throw new RuntimeException('Rental 1 Format can not contain null value.');
    }

    public function visitString($data, array $type, Context $context)
    {
        $this->content .= strtoupper($data);
    }

    public function visitBoolean($data, array $type, Context $context)
    {
        throw new RuntimeException('Rental 1 Format can not contain boolean value.');
    }

    public function visitDouble($data, array $type, Context $context)
    {
        $this->content .= $data;
    }

    public function visitInteger($data, array $type, Context $context)
    {
        $this->content .= $data;
    }

    public function visitArray($data, array $type, Context $context)
    {
        foreach ($data as $k => $v) {
            $this->navigator->accept($v, $this->getElementType($type), $context);
        }
    }

    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {

    }

    public function visitProperty(PropertyMetadata $metadata, $data, Context $context)
    {
        $value = $metadata->getValue($data);

        $this->navigator->accept($value, $metadata->type, $context);
    }

    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $this->content .= "\r\n";
    }

    public function setNavigator(GraphNavigator $navigator)
    {
        $this->navigator = $navigator;
    }

    public function getNavigator()
    {
        return $this->navigator;
    }

    public function getResult()
    {
        return $this->content;
    }
}
