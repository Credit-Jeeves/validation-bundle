<?php

namespace RentJeeves\TenantBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TenantBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'ApplicantBundle';
    }
}
