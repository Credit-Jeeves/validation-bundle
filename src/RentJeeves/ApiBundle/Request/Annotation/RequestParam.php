<?php

namespace RentJeeves\ApiBundle\Request\Annotation;

use FOS\RestBundle\Controller\Annotations\RequestParam as Base;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestParam extends Base
{
    /** @var mixed */
    public $encoder = null;
}
