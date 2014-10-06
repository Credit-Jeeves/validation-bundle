<?php

namespace RentJeeves\OAuthServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OAuthServerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'FOSOAuthServerBundle';
    }
}
