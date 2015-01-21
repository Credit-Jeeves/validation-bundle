<?php
namespace RentJeeves\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @DI\Service("core.twig.extension")
 * @DI\Tag("twig.extension")
 */
class Extension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('array_values', [$this, 'arrayValues']),
            new \Twig_SimpleFilter('phone_number', [$this, 'phoneNumber']),
            new \Twig_SimpleFilter('ordinal_number', [$this, 'ordinalNumber']),
        );
    }

    public function arrayValues($array)
    {
        return array_values($array);
    }

    public function phoneNumber($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        return ($phone) ? substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4) : '';
    }

    public function ordinalNumber($number)
    {
        if (!is_numeric($number)) {
            return $number;
        }
        // Special case "teenth"
        if (($number / 10) % 10 != 1) {
            // Handle 1st, 2nd, 3rd
            switch ($number % 10) {
                case 1: return $number . 'st';
                case 2: return $number . 'nd';
                case 3: return $number . 'rd';
            }
        }
        // Everything else is "nth"
        return $number . 'th';
    }

    public function getName()
    {
        return 'core_twig_extension';
    }
}
