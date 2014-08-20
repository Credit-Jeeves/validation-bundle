<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

class SkipPropertyExclusionStrategy implements ExclusionStrategyInterface
{
    private $skipProperties = [];

    private $skipCompare;

    private $useCompare = false;

    public function __construct(array $skipProperties, $useCompare = false, $skipCompare = null)
    {
        $this->skipProperties = $skipProperties;
        $this->useCompare = (bool) $useCompare;
        $this->skipCompare = $skipCompare;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context, $object = null)
    {
        $name = $property->serializedName ? $property->serializedName : $property->name;

        if (in_array($name, $this->skipProperties)) {
            if ($this->useCompare && $object) {
                $value = $property->getValue($object);

                return ($value === $this->skipCompare);
            }

            return true;
        }

        return false;
    }
}
