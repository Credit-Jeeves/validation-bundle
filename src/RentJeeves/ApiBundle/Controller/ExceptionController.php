<?php

namespace RentJeeves\ApiBundle\Controller;

use FOS\RestBundle\Controller\ExceptionController as BaseExceptionController;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use RentJeeves\ApiBundle\ErrorHandler\ExceptionWrapper;

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
        /** @var ExceptionWrapperHandlerInterface $exceptionWrapperHandler */
        $exceptionWrapperHandler = $this->container->get('fos_rest.view.exception_wrapper_handler');
        return $exceptionWrapperHandler->wrap($parameters);
    }
}
