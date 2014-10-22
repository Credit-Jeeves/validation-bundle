<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;

class ApiExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return (new ExceptionWrapper($data))->getErrors();
    }
}
