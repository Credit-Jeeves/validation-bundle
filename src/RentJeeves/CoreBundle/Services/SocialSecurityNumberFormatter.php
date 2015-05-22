<?php

namespace RentJeeves\CoreBundle\Services;

class SocialSecurityNumberFormatter
{
    const SSN_LENGTH = 9;

    /**
     * @param string $ssn
     * @return string on format 'NNN-NN-NNNN'
     */
    public static function formatWithDashes($ssn)
    {
        $ssn = self::formatToDigitsOnly($ssn);

        if (strlen($ssn) < self::SSN_LENGTH) {
            return $ssn;
        }

        return sprintf('%s-%s-%s', substr($ssn, 0, 3), substr($ssn, 3, 2), substr($ssn, 5));
    }

    /**
     * @param string $ssn
     * @return number
     */
    public static function formatToDigitsOnly($ssn)
    {
        return preg_replace('/\D/', '', $ssn);
    }
}
