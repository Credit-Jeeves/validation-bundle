<?php

namespace RentJeeves\CoreBundle\Services;

use FOS\UserBundle\Util\CanonicalizerInterface;

/**
 * Override default FOSUserCanonicalizer only for "null"
 */
class EmailCanonicalizer implements CanonicalizerInterface
{
    public function canonicalize($string)
    {
        return $string === null ? null : mb_convert_case($string, MB_CASE_LOWER, mb_detect_encoding($string));
    }
}
