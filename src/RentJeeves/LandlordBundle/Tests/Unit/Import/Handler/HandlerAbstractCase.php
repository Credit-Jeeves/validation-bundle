<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class HandlerAbstractCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCheckTenantStatus()
    {
        $this->load(true);
        $handler = new HandlerTest();
        $handlerTestReflection = new \ReflectionClass($handler);
        $this->writeAttribute($handler, 'currentImportModel', $import = new Import());
        $this->writeAttribute($handler, 'translator', $this->getContainer()->get('translator'));

        /** @var StorageCsv $storageCsv */
        $storageCsv = $this->getContainer()->get('accounting.import.storage.csv');
        $storageCsv->setDateFormat('Y-m-d');
        $this->writeAttribute($handler, 'storage', $storageCsv);

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
        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($groupModel);
        $this->assertFalse($groupModel->getHolding()->isAllowedFutureContract());
        $groupModel->getHolding()->setIsAllowedFutureContract(true);
        $this->getEntityManager()->flush();
        $this->writeAttribute($handler, 'group', $groupModel);

        $row = [
            MappingAbstract::KEY_TENANT_STATUS => 'f',
            MappingAbstract::KEY_MOVE_OUT => '',
        ];
        $import->setMoveOut(null);
        $import->setIsSkipped(false);
        $checkTenantStatus->invoke($handler, $row);
        $this->assertNull($import->getMoveOut());
        $this->assertEquals(false, $import->isSkipped());

        $groupModel->getHolding()->setIsAllowedFutureContract(false);
        $checkTenantStatus->invoke($handler, $row);
        $this->assertEquals(true, $import->isSkipped());
    }

    /**
     * @test
     */
    public function shouldCheckPropertyByExternalPropertyId()
    {
        $handler = new HandlerTest();
        $handlerTestReflection = new \ReflectionClass($handler);
        $storageCsv = $this->getMock(
            'RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv',
            ['isMultipleProperty'],
            [],
            '',
            false
        );
        $storageCsv->expects($this->atLeast(3))
            ->method('isMultipleProperty')
            ->withAnyParameters(true);

        $this->writeAttribute($handler, 'storage', $storageCsv);
        $this->writeAttribute($handler, 'logger', $this->getContainer()->get('logger'));
        $this->writeAttribute($handler, 'em', $this->getEntityManager());

        $getPropertyByExternalPropertyId = $handlerTestReflection->getMethod('getPropertyByExternalPropertyId');
        $getPropertyByExternalPropertyId->setAccessible(true);

        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($groupModel);
        $externalPropertyId = PaymentClientCase::PROPERTY_ID;
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, $externalPropertyId);
        $this->assertInstanceOf('RentJeeves\DataBundle\Entity\Property', $property);
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, null);
        $this->assertFalse($property);
        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Rent Group');
        $this->assertNotEmpty($groupModel);
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, $externalPropertyId);
        $this->assertFalse($property);
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
