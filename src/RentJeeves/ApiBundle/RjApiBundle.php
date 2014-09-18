<?php

namespace RentJeeves\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RjApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'FOSRestBundle';
    }
}
