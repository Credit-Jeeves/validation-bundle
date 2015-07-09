<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Handler;

use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\TestBundle\BaseTestCase;

class HandlerAbstractCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckTenantStatus()
    {
        $handler = new HandlerTest();
        $handlerTestReflection = new \ReflectionClass($handler);
        $currentImportModel = $handlerTestReflection->getProperty('currentImportModel');
        $currentImportModel->setAccessible(true);
        $currentImportModel->setValue($handler, $import = new Import());

        $currentImportModel = $handlerTestReflection->getProperty('translator');
        $currentImportModel->setAccessible(true);
        $currentImportModel->setValue($handler, $this->getContainer()->get('translator'));
        /** @var StorageCsv $storageCsv */
        $storageCsv = $this->getContainer()->get('accounting.import.storage.csv');
        $storageCsv->setDateFormat('Y-m-d');
        $storage = $handlerTestReflection->getProperty('storage');
        $storage->setAccessible(true);
        $storage->setValue($handler, $storageCsv);

        $checkTenantStatus = $handlerTestReflection->getMethod('checkTenantStatus');
        $checkTenantStatus->setAccessible(true);

        $row = [
            MappingAbstract::KEY_TENANT_STATUS => 'p',
            MappingAbstract::KEY_MOVE_OUT => ''
        ];

        $today = new \DateTime();
        $checkTenantStatus->invoke($handler, $row);

        $this->assertEquals($today->format('ymd'), $import->getMoveOut()->format('ymd'));
        $this->assertEquals(false, $import->isSkipped());

        $import->setMoveOut(null);

        $row[MappingAbstract::KEY_TENANT_STATUS] = 'c';

        $checkTenantStatus->invoke($handler, $row);

        $this->assertEquals(null, $import->getMoveOut());
        $this->assertEquals(false, $import->isSkipped());

        $row[MappingAbstract::KEY_TENANT_STATUS] = 'k';
        $checkTenantStatus->invoke($handler, $row);

        $this->assertEquals(null, $import->getMoveOut());
        $this->assertEquals(true, $import->isSkipped());
        $this->assertEquals('error.tenant.status', $import->getSkippedMessage());
        $import->setIsSkipped(false);

        $moveOut = new \DateTime('-5 days');
        $row = [
            MappingAbstract::KEY_TENANT_STATUS => 'p',
            MappingAbstract::KEY_MOVE_OUT => $moveOut->format('Y-m-d')
        ];

        $checkTenantStatus->invoke($handler, $row);
        $this->assertNotNull($import->getMoveOut());
        $this->assertEquals($moveOut->format('ymd'), $import->getMoveOut()->format('ymd'));
        $this->assertEquals(false, $import->isSkipped());
    }

    /**
     * @test
     */
    public function shouldCheckUnitMappingByExternalUnitId()
    {
        $this->load(true);
        $handler = new HandlerTest();
        $handlerTestReflection = new \ReflectionClass($handler);
        $currentImportModel = $handlerTestReflection->getProperty('em');
        $currentImportModel->setAccessible(true);
        $currentImportModel->setValue($handler, $em = $this->getEntityManager());

        $logger = $handlerTestReflection->getProperty('logger');
        $logger->setAccessible(true);
        $logger->setValue($handler, $this->getContainer()->get('logger'));

        $unitMappingByExternalUnitId = $handlerTestReflection->getMethod('getUnitMappingByExternalUnitId');
        $unitMappingByExternalUnitId->setAccessible(true);

        $group = $em->getRepository('DataBundle:Group')->findOneBy(['name' => 'Test Rent Group']);
        $this->assertNotNull($group);
        /** @var UnitMapping $unitMappingNotNull */
        $unitMappingNotNull = $unitMappingByExternalUnitId->invoke($handler, $group, 'AAABBB-7');
        $this->assertNotNull($unitMappingNotNull, 'Checked look up UnitMapping failed. Must Find.');
        $unitMappingNull = $unitMappingByExternalUnitId->invoke($handler, $group, 'AAABBB-1');
        $this->assertNull($unitMappingNull, 'Checked look up UnitMapping failed. Must NOT Find.');

        $unitMapping = new UnitMapping();
        $unit = new Unit();
        $unit->setName('Test');
        $unit->setGroup($group = $unitMappingNotNull->getUnit()->getGroup());
        $unit->setHolding($group->getHolding());
        $unit->setProperty($group->getGroupProperties()->first());
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId($unitMappingNotNull->getExternalUnitId());
        $em->persist($unitMapping);
        $em->persist($unit);
        $em->flush();

        $unitMappingNull = $unitMappingByExternalUnitId->invoke($handler, $group, 'AAABBB-7');
        $this->assertNull($unitMappingNull, 'Checked exception NonUniqueResultException in method - failed');
    }
}
