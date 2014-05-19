<?php
namespace RentJeeves\CheckoutBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use DateTime;
use Exception;

class DateTimeToStringTransformer implements DataTransformerInterface
{
    /**
     * Transforms a DateTime object to a string.
     *
     * @param DateTime|null $dateTime
     *
     * @return string|null
     */
    public function transform($dateTime)
    {
        if (null === $dateTime) {
            return null;
        }

        return $dateTime->format('Y-m-d');
    }

    /**
     * Transforms a string to a DateTime object.
     *
     * @param int $string
     *
     * @throws TransformationFailedException if string is invalid.
     *
     * @return DateTime|null
     */
    public function reverseTransform($string)
    {
        if (!$string) {
            return null;
        }
        try {
            $dateTime = new DateTime($string);
        } catch (Exception $e) {
            throw new TransformationFailedException(
                sprintf(
                    'A string "%s" is not valid date!',
                    $string
                ),
                0,
                $e
            );
        }

        return $dateTime;
    }
}
