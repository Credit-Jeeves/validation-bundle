<?php

namespace RentJeeves\CoreBundle\Validator;

use Symfony\Component\Validator\Constraints\Date;
/**
 * @Annotation
 */
class DateWithFormat extends Date
{
    public $format = 'm/d/Y';

    public function validatedBy()
    {
        return 'date_with_format_validator';
    }
}
