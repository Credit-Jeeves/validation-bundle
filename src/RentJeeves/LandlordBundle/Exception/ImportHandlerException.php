<?php

namespace RentJeeves\LandlordBundle\Exception;

use \Exception;

class ImportHandlerException extends Exception
{
    public function setUniqueKey($key)
    {
        $this->message = sprintf(
            'Unique key %s, exception message: %s',
            $key,
            $this->getMessage()
        );
    }
}
