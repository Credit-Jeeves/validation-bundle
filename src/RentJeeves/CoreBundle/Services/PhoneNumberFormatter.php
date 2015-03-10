<?php

namespace RentJeeves\CoreBundle\Services;

class PhoneNumberFormatter
{
    const PHONE_NUMBER_LENGTH = 10;

    /**
     * Expects number to be a 10 digits number.
     * Returns number formatted by template (NNN) NNN-NNNN.
     *
     * @param string $number
     * @return string
     */
    public static function formatWithBracketsAndDash($number)
    {
        $number = self::formatToDigitsOnly($number);

        if (strlen($number) != self::PHONE_NUMBER_LENGTH) {
            return $number;
        }

        $cityCode = substr($number, 0, 3);
        $firstPart = substr($number, 3, 3);
        $secondPart = substr($number, 6);

        return sprintf('(%s) %s-%s', $cityCode, $firstPart, $secondPart);
    }

    /**
     * Cleans up all non numeric symbols in number.
     *
     * @param string $number
     * @return string
     */
    public static function formatToDigitsOnly($number)
    {
        return preg_replace('/\D/', '', $number);
    }
}
