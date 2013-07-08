<?php
namespace CreditJeeves\DataBundle\Tests;

use CreditJeeves\TestBundle\BaseTestCase;

class DBTest extends BaseTestCase
{
    /**
     * @test
     */
    public function fixturesLoad()
    {
        $this->load(true);
    }
}
