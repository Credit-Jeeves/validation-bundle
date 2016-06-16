<?php

namespace RentJeeves\CoreBundle\Tests\Functional\AddressLookup\Cache;

use RentJeeves\CoreBundle\Services\AddressLookup\Cache\SmartyStreetsCacheService;
use RentJeeves\DataBundle\Entity\SmartyStreetsCache;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class SmartyStreetsCacheServiceCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldAddRowToDbWhenCallSaveForNewCacheId()
    {
        $this->load(true);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(50, $allCache, 'Check fixtures: fixtures should contain data for SmartyStreetsCache');

        $result = $this->getSmartyStreetsCacheService()->save('test', 'test');

        $this->assertTrue($result, 'Cache with key `test` is not saved');

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(51, $allCache, 'SS cache should be added for empty data');
    }

    /**
     * @test
     */
    public function shouldNotAddRowToDbWhenCallSaveForNewEmptyCacheId()
    {
        $this->load(true);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(50, $allCache, 'Check fixtures: fixtures should contain data for SmartyStreetsCache');

        $this->getSmartyStreetsCacheService()->save('', 'test');

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(50, $allCache, 'SS cache should not be added for empty data');
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenCallSaveForExistingCacheId()
    {
        $this->load(true);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(50, $allCache, 'Check fixtures: fixtures should contain data for SmartyStreetsCache');

        $result = $this->getSmartyStreetsCacheService()->save('test', 'test');

        $this->assertTrue($result, 'Cache with key `test` is not saved');
        // save again
        $result = $this->getSmartyStreetsCacheService()->save('test', 'test');
        $this->assertFalse($result, 'Cache should not be saved twice');
    }

    /**
     * @test
     */
    public function shouldRemoveExistRowForDelete()
    {
        $this->load(true);

        $cache = new SmartyStreetsCache();
        $cache->setId('test');
        $cache->setValue('test');

        $this->getEntityManager()->persist($cache);
        $this->getEntityManager()->flush($cache);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(51, $allCache, 'Check creating new object SmartyStreetsCache');

        $result = $this->getSmartyStreetsCacheService()->delete('test');

        $this->assertTrue($result, 'Cache with key `test` is not deleted');

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(50, $allCache, 'Data not removed');
    }

    /**
     * @test
     */
    public function shouldReturnValueIfRowExistForFetch()
    {
        $this->load(true);

        $cache = new SmartyStreetsCache();
        $cache->setId('test');
        $cache->setValue('testValue');

        $this->getEntityManager()->persist($cache);
        $this->getEntityManager()->flush($cache);

        $result = $this->getSmartyStreetsCacheService()->fetch('test');

        $this->assertEquals('testValue', $result);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRowNotExistForFetch()
    {
        $this->load(true);

        $result = $this->getSmartyStreetsCacheService()->fetch('test');

        $this->assertFalse($result, 'Cache returned incorrect result');
    }

    /**
     * @test
     */
    public function shouldReturnCorrectResultForContains()
    {
        $this->load(true);

        $cache = new SmartyStreetsCache();
        $cache->setId('test');
        $cache->setValue('test');

        $this->getEntityManager()->persist($cache);
        $this->getEntityManager()->flush($cache);

        $result = $this->getSmartyStreetsCacheService()->contains('test');

        $this->assertTrue($result, 'Method `contains` returned incorrect result');

        $this->getEntityManager()->remove($cache);
        $this->getEntityManager()->flush($cache);

        $result = $this->getSmartyStreetsCacheService()->contains('test');
        $this->assertFalse($result, 'Method `contains` returned incorrect result');
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\Cache\SmartyStreetsCacheService
     */
    protected function getSmartyStreetsCacheService()
    {
        return new SmartyStreetsCacheService($this->getEntityManager());
    }
}
