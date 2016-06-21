<?php

namespace RentJeeves\ApiBundle\Tests\Data\Services\ResourceUrlGenerator;

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
