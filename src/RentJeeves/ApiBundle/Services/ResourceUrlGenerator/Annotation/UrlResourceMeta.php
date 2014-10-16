<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class UrlResourceMeta
{
    /** @var mixed */
    public $prefix;

    /** @var string */
    public $actionName;

    /** @var string */
    public $attributeName = 'id';

    /** @var  mixed */
    public $encoder;
}
