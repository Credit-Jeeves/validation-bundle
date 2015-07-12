<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group;
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
        $this->load(true);
        $handler = new HandlerTest();
        $handlerTestReflection = new \ReflectionClass($handler);
        $currentImportModel = $handlerTestReflection->getProperty('currentImportModel');
        $currentImportModel->setAccessible(true);
        $currentImportModel->setValue($handler, $import = new Import());

        $currentImportModel = $handlerTestReflection->getProperty('translator');
        $currentImportModel->setAccessible(true);
        $currentImportModel->setValue($handler, $this->getContainer()->get('translator'));

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
        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($groupModel);
        $this->assertFalse($groupModel->getHolding()->isAllowedFutureContract());
        $groupModel->getHolding()->setIsAllowedFutureContract(true);
        $this->getEntityManager()->flush();
        $groupReflection = $handlerTestReflection->getProperty('group');
        $groupReflection->setAccessible(true);
        $groupReflection->setValue($handler, $groupModel);

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
            ->withAnyParameters(
                $this->callback(
                    function () {
                        return true;
                    }
                )
            );

        $storage = $handlerTestReflection->getProperty('storage');
        $storage->setAccessible(true);
        $storage->setValue($handler, $storageCsv);

        $logger = $handlerTestReflection->getProperty('logger');
        $logger->setAccessible(true);
        $logger->setValue($handler, $this->getContainer()->get('logger'));

        $em = $handlerTestReflection->getProperty('em');
        $em->setAccessible(true);
        $em->setValue($handler, $this->getEntityManager());

        $getPropertyByExternalPropertyId = $handlerTestReflection->getMethod('getPropertyByExternalPropertyId');
        $getPropertyByExternalPropertyId->setAccessible(true);

        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($groupModel);
        $externalPropertyId = 'rnttrk01';
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
}
