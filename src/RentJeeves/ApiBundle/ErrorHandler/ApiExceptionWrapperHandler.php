<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("fos_rest.view.exception_wrapper_handler")
 */
class ApiExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    /**
     * @DI\Inject("translator")
     */
    public $translator;
    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return (new ExceptionWrapper($data, $this->translator))->getErrors();
    }
}
