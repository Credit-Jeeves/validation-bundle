<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use PhpOption;

class SkipPropertyExclusionStrategy implements ExclusionStrategyInterface
{
    private $skipProperties = [];

    private $skipCompares;

    private $useCompare = [];

    public function __construct(array $skipProperties, array $skipCompares, $useCompare = false)
    {
        $this->skipProperties = $skipProperties;
        $this->useCompare = (bool) $useCompare;
        $this->skipCompares = $skipCompares;
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
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        $name = $property->serializedName ? $property->serializedName : $property->name;
        $object = $this->getObject($context);

        if (in_array($name, $this->skipProperties)) {
            if ($this->useCompare && $object &&
                ($property->serializedName && method_exists($object, $property->getter))
            ) {
                $value = $property->getValue($object);
                $isSkip = false;

                foreach ($this->skipCompares as $skipCompare) {
                    if ($value === $skipCompare) {
                        $isSkip = true;
                    }
                }

                return $isSkip;
            }

            return true;
        }

        return false;
    }

    protected function getObject($context)
    {
        $object = $context->attributes->get('object');
        if ($object instanceof PhpOption\Some) {
            return $object->get();
        }

        return null;
    }
}
