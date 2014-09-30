<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use RentJeeves\ApiBundle\ErrorHandler\ExceptionWrapper;
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
