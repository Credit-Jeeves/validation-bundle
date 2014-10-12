<?php

namespace RentJeeves\ApiBundle\Request\Annotation;

use FOS\RestBundle\Controller\Annotations\Param;

/**
 * @Annotation
 * @Target("METHOD")
 */
class AttributeParam extends Param
{
    /** @var  string */
    public $encoder;
}
