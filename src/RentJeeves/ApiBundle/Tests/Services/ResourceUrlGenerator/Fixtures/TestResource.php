<?php

namespace RentJeeves\ApiBundle\Tests\Services\ResourceUrlGenerator\Fixtures;

use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;

/**
 * @UrlResourceMeta(
 *      prefix = "",
 *      actionName = "fos_user_security_login"
 * )
 */
class TestResource
{
    public $id = 1;
}
