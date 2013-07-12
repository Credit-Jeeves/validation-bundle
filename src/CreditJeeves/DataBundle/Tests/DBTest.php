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
        $khepin = static::getContainer()->get('khepin.yaml_loader');
        $khepin->purgeDatabase('orm');
        $khepin->loadFixtures();
    }
}
