<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use RentJeeves\TestBundle\Mocks\CommonSystemMocks;
use Doctrine\ORM\NonUniqueResultException;

class HandlerAbstractCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    protected $systemsMocks;
    protected $exceptionCatcherMock;
    protected $loggerMock;
    protected $externalPropertyId;

    protected function setUp()
    {
        $this->systemsMocks = new CommonSystemMocks();
        $this->exceptionCatcherMock = $this->systemsMocks->getExceptionCatcherMock();
        $this->loggerMock = $this->systemsMocks->getLoggerMock();
        $this->externalPropertyId = PaymentClientCase::PROPERTY_ID;
    }

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
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, $this->externalPropertyId);
        $this->assertInstanceOf('RentJeeves\DataBundle\Entity\Property', $property);
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, null);
        $this->assertNull($property);
        /** @var Group $groupModel */
        $groupModel = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Rent Group');
        $this->assertNotEmpty($groupModel);
        $property = $getPropertyByExternalPropertyId->invoke($handler, $groupModel, $this->externalPropertyId);
        $this->assertNull($property);
    }

    /**
     * @test
     */
    public function shouldNotReturnPropertyIfDuplicateExternalIdsFound()
    {
        $handler = $this->getHandlerMock();
        $handlerTestReflection = new \ReflectionClass($handler);

        $this->writeAttribute($handler, 'storage', $this->getStorageMock(true));
        $this->writeAttribute($handler, 'logger', $this->loggerMock);

        $getPropertyByExternalPropertyId = $handlerTestReflection->getMethod('getPropertyByExternalPropertyId');
        $getPropertyByExternalPropertyId->setAccessible(true);

        $property = $getPropertyByExternalPropertyId->invoke(
            $handler,
            $this->systemsMocks->getGroupMock(),
            $this->externalPropertyId
        );

        $this->assertNull($property, "We should get null if there is a duplicate external ID for property");

    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\LandlordBundle\Tests\Unit\Import\Handler\HandlerTest
     */
    public function getHandlerMock()
    {
        $mockObj = $this->getMock(
            '\RentJeeves\LandlordBundle\Tests\Unit\Import\Handler\HandlerTest',
            ['findPropertyByExternalId'],
            [],
            '',
            false
        );
        $mockObj->expects($this->any())
            ->method('findPropertyByExternalId')
            ->will($this->throwException(new NonUniqueResultException));

        return $mockObj;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\LandlordBundle\Tests\Unit\Import\Handler\HandlerTest
     */
    public function getStorageMock($isMultiProperty)
    {
        $mockObj = $this->getMock(
            '\RentJeeves\LandlordBundle\Tests\Unit\Import\Handler\HandlerTest',
            ['isMultipleProperty'],
            [],
            '',
            false
        );

        $mockObj->expects($this->once())
            ->method('isMultipleProperty')
            ->withAnyParameters($isMultiProperty);

        return $mockObj;
    }
}
