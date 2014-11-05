<?php

namespace RentJeeves\ApiBundle\Forms\DataTransformer;

use RentJeeves\ApiBundle\Forms\Enum\ReportingType;
use Symfony\Component\Form\DataTransformerInterface;

class ReportingEnableTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value) {
            return ReportingType::ENABLED;
        }

        return ReportingType::DISABLED;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return ReportingType::getMapValue($value);
    }
}
