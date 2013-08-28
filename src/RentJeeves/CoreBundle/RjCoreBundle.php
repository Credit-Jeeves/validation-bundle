<?php

namespace RentJeeves\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RjCoreBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'CoreBundle';
    }
}
