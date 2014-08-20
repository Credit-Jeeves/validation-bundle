<?php

namespace RentJeeves\CoreBundle\Visitor;

use JMS\Serializer\Context;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\DiExtraBundle\Annotation as DI;
use PhpOption;

/**
 * @DI\Service("jms_serializer.yardi_serialization_visitor")
 * @DI\Tag("jms_serializer.serialization_visitor", attributes = {"format" = "yardi"})
 */
class YardiSerializationVisitor extends XmlSerializationVisitor
{
    protected $useSkipTag = false;

    protected $skipCompare = '';

    /**
     * @DI\InjectParams({
     *     "namingStrategy" = @DI\Inject("jms_serializer.naming_strategy")
     * })
     */
    public function __construct(PropertyNamingStrategyInterface $namingStrategy)
    {
        parent::__construct($namingStrategy);
    }

    public function visitSimpleString($data, array $type, Context $context)
    {
        if ($this->checkIsVisiting($data, $type, $context)) {
            return parent::visitSimpleString($data, $type, $context);
        }
        return;
    }

    public function visitString($data, array $type, Context $context)
    {
        if ($this->checkIsVisiting($data, $type, $context)) {
            return parent::visitString($data, $type, $context);
        }
        return;
    }

    protected function checkIsVisiting($data, array $type, Context $context)
    {
        if ($this->useSkipTag && $data === $this->skipCompare) {
            return false;
        }

        return true;
    }

    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        $useSkipTag = $context->attributes->get('use_skip_tag');
        $skipCompare = $context->attributes->get('skip_tag_compare');

        if ($useSkipTag instanceof PhpOption\Some) {
            $this->useSkipTag =  $useSkipTag->get();
        }

        if ($skipCompare instanceof PhpOption\Some) {
            $this->skipCompare = $skipCompare->get();
        }

        parent::startVisitingObject($metadata, $data, $type, $context);
    }
}
 