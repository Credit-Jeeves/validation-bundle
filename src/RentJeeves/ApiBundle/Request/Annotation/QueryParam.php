<?php

namespace RentJeeves\ApiBundle\Request\Annotation;

use FOS\RestBundle\Controller\Annotations\QueryParam as Base;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class QueryParam extends Base
{
    /** @var string */
    public $encoder;
}
