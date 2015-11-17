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
    public function shouldAddRawToDbForSave()
    {
        $this->load(true);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(0, $allCache, 'Check fixtures');

        $result = $this->getSmartyStreetsCacheService()->save('test', 'test');

        $this->assertTrue($result);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(1, $allCache, 'Data not saved in db');
    }

    /**
     * @test
     */
    public function shouldRemoveExistRawForDelete()
    {
        $this->load(true);

        $SSCache = new SmartyStreetsCache();
        $SSCache->setId('test');
        $SSCache->setValue('test');

        $this->getEntityManager()->persist($SSCache);
        $this->getEntityManager()->flush($SSCache);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(1, $allCache, 'Check creating new object SmartyStreetsCache');

        $result = $this->getSmartyStreetsCacheService()->delete('test');

        $this->assertTrue($result);

        $allCache = $this->getEntityManager()->getRepository('RjDataBundle:SmartyStreetsCache')->findAll();
        $this->assertCount(0, $allCache, 'Data not removed');
    }

    /**
     * @test
     */
    public function shouldReturnValueIfRawExistForFetch()
    {
        $this->load(true);

        $SSCache = new SmartyStreetsCache();
        $SSCache->setId('test');
        $SSCache->setValue('testValue');

        $this->getEntityManager()->persist($SSCache);
        $this->getEntityManager()->flush($SSCache);

        $result = $this->getSmartyStreetsCacheService()->fetch('test');

        $this->assertEquals('testValue', $result);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfRawNotExistForFetch()
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

        $SSCache = new SmartyStreetsCache();
        $SSCache->setId('test');
        $SSCache->setValue('test');

        $this->getEntityManager()->persist($SSCache);
        $this->getEntityManager()->flush($SSCache);

        $result = $this->getSmartyStreetsCacheService()->contains('test');

        $this->assertTrue($result, 'Method `contains` returned incorrect result');

        $this->getEntityManager()->remove($SSCache);
        $this->getEntityManager()->flush($SSCache);

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
