<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

class SinglePropertyConstraint extends Constraint
{
    public $commonMessage = 'property.error.can_not_be_added';
    public $emptyUnitsMessage = 'units.error.add_or_mark_single';

    public function validatedBy()
    {
        return 'single_property_validator';
    }
}
