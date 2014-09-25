<?php
namespace RentJeeves\ExperianBundle\Tests\Functional\NetConnect;

use CreditJeeves\ExperianBundle\Tests\Functional\NetConnect\PreciseIDCase as Base;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PreciseIDCase extends Base
{
    protected $preciseIDClass = 'RentJeeves\ExperianBundle\NetConnect\PreciseID';

    /**
     * @var string
     */
    const APP = 'AppRj';
}
