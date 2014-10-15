<?php

namespace RentJeeves\ApiBundle\Tests\Services\ResourceUrlGenerator;

use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlGenerateMeta;

/**
 * @UrlGenerateMeta(
 *      prefix = "",
 *      actionName = "fos_user_security_login"
 * )
 */
class TestResource
{
    public function getId()
    {
        return 1;
    }
}
