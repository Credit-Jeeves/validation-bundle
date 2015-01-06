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
            new \Twig_SimpleFilter('array_values', array($this, 'arrayValues')),
            new \Twig_SimpleFilter('phone_number', array($this, 'phoneNumber')),
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

    public function getName()
    {
        return 'core_twig_extension';
    }
}
