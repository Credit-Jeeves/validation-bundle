<?php

namespace RentJeeves\CoreBundle\Tests\Unit\PaymentProcessorMigration;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\PaymentProcessorMigration\CsvExporter;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper\AciProfileMapper;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Component\Validator\ConstraintViolationList;

class CsvExporterCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * remove created file
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (true === file_exists($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return __DIR__ . '/test.csv';
    }

    /**
     * @test
     */
    public function shouldCreateFileWithDataForHoldingIfSendHoldings()
    {
        $holding = new Holding();
        $this->writeIdAttribute($holding, 1);
        $holding2 = new Holding();
        $this->writeIdAttribute($holding2, 2);

        $profile1 = new AciImportProfileMap();
        $profile1->setUser(new Tenant());
        $profile2 = new AciImportProfileMap();
        $profile2->setGroup(new Group());

        $emResponse = [$profile1, $profile2];
        $em = $this->getEmMock();
        $aciProfileMapRepositoryMock = $this->getAciProfileMapRepositoryMock();
        // For holding
        $aciProfileMapRepositoryMock->expects($this->once())
            ->method('findAllByHoldingIds')
            ->with($this->equalTo([1, 2]))
            ->will($this->returnValue($emResponse));

        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($aciProfileMapRepositoryMock));

        $mapper = $this->getMapperMock();
        $mapper->expects($this->once())
            ->method('mapUser')
            ->will($this->returnValue([$consumerRecord = new ConsumerRecord()]));

        $mapper->expects($this->once())
            ->method('mapGroup')
            ->will($this->returnValue([$fundingRecord = new FundingRecord()]));

        $validator = $this->getValidatorMock();
        $validator->expects($this->exactly(2))
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $serializer = $this->getSerializerMock();
        $serializer->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue($result = "csvDataHere"));

        $csvExporter = new CsvExporter($em, $mapper, $serializer, $validator);
        $csvExporter->export($this->getFilePath(), [$holding, $holding2]);

        $this->assertContains($result, file_get_contents($this->getFilePath()));
    }

    /**
     * @test
     */
    public function shouldCreateFileWithDataForAllHoldingIfNotSendHoldings()
    {
        $holding = null;

        $profile1 = new AciImportProfileMap();
        $profile1->setUser(new Tenant());

        $emResponse = [$profile1];
        $em = $this->getEmMock();
        $aciProfileMapRepositoryMock = $this->getAciProfileMapRepositoryMock();
        // For all holdings
        $aciProfileMapRepositoryMock->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($emResponse));

        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($aciProfileMapRepositoryMock));

        $mapper = $this->getMapperMock();
        $mapper->expects($this->once())
            ->method('mapUser')
            ->will($this->returnValue([$consumerRecord = new ConsumerRecord()]));
        $mapper->expects($this->never())
            ->method('mapGroup');

        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $serializer = $this->getSerializerMock();
        $serializer->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue($result = "csvDataHere"));

        $csvExporter = new CsvExporter($em, $mapper, $serializer, $validator);
        $csvExporter->export($this->getFilePath(), $holding);

        $fileData = file_get_contents($this->getFilePath());
        $this->assertContains($result, $fileData);
    }

    /**
     * @return \Doctrine\ORM\EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEmMock()
    {
        return $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false);
    }

    /**
     * @return AciProfileMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMapperMock()
    {
        return $this->getMock(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper\AciProfileMapper',
            ['mapUser', 'mapGroup'],
            [],
            '',
            false
        );
    }

    /**
     * @return \JMS\Serializer\Serializer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSerializerMock()
    {
        return $this->getMock(
            '\JMS\Serializer\Serializer',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \Symfony\Component\Validator\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValidatorMock()
    {
        return $this->getMock(
            '\Symfony\Component\Validator\Validator',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\AciImportProfileMapRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAciProfileMapRepositoryMock()
    {
        return $this->getMock(
            '\RentJeeves\DataBundle\Entity\AciImportProfileMapRepository',
            [],
            [],
            '',
            false
        );
    }
}
