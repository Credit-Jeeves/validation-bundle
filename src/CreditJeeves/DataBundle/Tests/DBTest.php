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
        $this->getContainer()->get('khepin.yaml_loader')->purgeDatabase('orm');
        $this->getContainer()->get('khepin.yaml_loader')->loadFixtures('settings');
    }
}
