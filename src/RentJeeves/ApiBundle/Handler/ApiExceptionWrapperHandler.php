<?php

namespace RentJeeves\ApiBundle\Handler;

use CreditJeeves\ApiBundle\Util\ExceptionWrapper;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;

class ApiExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return new ExceptionWrapper($data);
    }
}
