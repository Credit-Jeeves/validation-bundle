<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\EventListener\LoggableListener as Base;
use RentJeeves\DataBundle\Entity\Contract;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class LoggableListener extends Base
{
    protected function isLoggable($object)
    {
        if ($object instanceof Contract) {
            return true;
        }
        return parent::isLoggable($object);
    }
}
