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
        );
    }

    public function arrayValues($array)
    {
        return array_values($array);
    }

    public function getName()
    {
        return 'core_twig_extension';
    }
}
