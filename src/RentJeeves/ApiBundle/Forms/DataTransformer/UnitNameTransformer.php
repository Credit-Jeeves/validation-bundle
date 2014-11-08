<?php

namespace RentJeeves\ApiBundle\Forms\DataTransformer;

use RentJeeves\DataBundle\Entity\Unit;
use Symfony\Component\Form\DataTransformerInterface;

class UnitNameTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value) && $value != '0') {
            return null;
        }

        if ($value === Unit::SINGLE_PROPERTY_UNIT_NAME) {
            return '';
        }

        return $value;
    }
}
