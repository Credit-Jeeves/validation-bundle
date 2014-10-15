<?php

namespace RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class UrlGenerateMeta
{
    /** @var mixed */
    public $prefix = null;

    /** @var string */
    public $actionName = null;

    /**
     * Means property for get when generate url like /url/{id}
     * @var string
     */
    public $id = 'id';

    /** @var  mixed */
    public $encoder = null;
}
