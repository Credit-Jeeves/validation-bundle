<?php

namespace RentJeeves\ApiBundle\Controller;

use FOS\RestBundle\Controller\ExceptionController as BaseExceptionController;
use CreditJeeves\ApiBundle\Util\ExceptionWrapper;

class ExceptionController extends BaseExceptionController
{
    /**
     * Creates a new ExceptionWrapper instance that can be overwritten by a custom
     * ExceptionController class.
     *
     * @param array $parameters Template parameters
     *
     * @return ExceptionWrapper ExceptionWrapper instance
     */
    protected function createExceptionWrapper(array $parameters)
    {
        return new ExceptionWrapper($parameters);
    }
}
