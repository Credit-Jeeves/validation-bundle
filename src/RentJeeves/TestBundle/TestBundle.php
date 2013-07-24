<?php

namespace RentJeeves\TestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TestBundle extends Bundle
{
    public function __construct()
    {
        $this->name = "RjTestBundle";
    }
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'TestBundle';
    }
}
