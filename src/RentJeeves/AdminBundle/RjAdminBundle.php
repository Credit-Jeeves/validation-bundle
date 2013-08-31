<?php

namespace RentJeeves\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RjAdminBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'AdminBundle';
    }
}
