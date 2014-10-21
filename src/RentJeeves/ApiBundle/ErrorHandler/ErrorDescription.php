<?php

namespace RentJeeves\ApiBundle\ErrorHandler;

use JMS\Serializer\Annotation as Serializer;

class ErrorDescription
{
    /**
     * @Serializer\Groups({"ApiErrors"})
     */
    public $parameter;

    /**
     * @Serializer\Groups({"ApiErrors"})
     */
    public $value;

    /**
     * @Serializer\Groups({"ApiErrors"})
     */
    public $message;
}
