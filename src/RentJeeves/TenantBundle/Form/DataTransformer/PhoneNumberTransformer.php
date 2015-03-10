<?php
namespace RentJeeves\TenantBundle\Form\DataTransformer;

use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;
use Symfony\Component\Form\DataTransformerInterface;

class PhoneNumberTransformer implements DataTransformerInterface
{
    /**
     * Formats number according to format (NNN) NNN-NNNN
     *
     * @param string $number
     * @return string
     */
    public function transform($number)
    {
        return PhoneNumberFormatter::formatWithBracketsAndDash($number);
    }

    /**
     * Removes non numeric symbols from phone number
     *
     * @param string $number
     * @return string
     */
    public function reverseTransform($number)
    {
        return PhoneNumberFormatter::formatToDigitsOnly($number);
    }
}
