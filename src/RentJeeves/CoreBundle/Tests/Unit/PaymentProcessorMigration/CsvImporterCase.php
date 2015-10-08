<?php

namespace RentJeeves\CoreBundle\Tests\Unit\PaymentProcessorMigration;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\PaymentProcessorMigration\CsvImporter;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer\EnrollmentResponseFileDeserializer;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Component\Validator\ConstraintViolationList;

class CsvImporterCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldImportEachModel()
    {
        $pathToFile = '/1.csv';
        $holding = new Holding();

        $firstRecord = new ConsumerResponseRecord();
        $firstRecord->setProfileId($firstRecordProfileId = 'test1');
        $secondRecord = new AccountResponseRecord();
        $secondRecord->setProfileId($secondRecordProfileId = 'test2');
        $returnArray = [$firstRecord, $secondRecord];

        $aciProfile = new AciImportProfileMap();
        $aciProfile->setUser(new Tenant());

        $aciMapRepositoryMock = $this->getAciMapRepositoryMock();
        $aciMapRepositoryMock->expects($this->exactly(2))
            ->method('find')
            ->withConsecutive(
                [$this->equalTo($firstRecordProfileId)],
                [$this->equalTo($secondRecordProfileId)]
            )
            ->will($this->returnValue($aciProfile));
        $contractRepositoryMock = $this->getContractRepositoryMock();
        $contractRepositoryMock->expects($this->exactly(2))
            ->method('findBy')
            ->will($this->returnValue(new Contract()));

        $em = $this->getEmMock();
        // Repositories
        $em->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:AciImportProfileMap'))
            ->will($this->returnValue($aciMapRepositoryMock));
        $em->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Contract'))
            ->will($this->returnValue($contractRepositoryMock));
        $em->expects($this->at(4))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:AciImportProfileMap'))
            ->will($this->returnValue($aciMapRepositoryMock));
        $em->expects($this->at(5))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Contract'))
            ->will($this->returnValue($contractRepositoryMock));

        $validator = $this->getValidatorMock();
        $validator->expects($this->exactly(2))
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $deserializer = $this->getDeserializerMock();
        $deserializer->expects($this->once())
            ->method('deserialize')
            ->with($this->equalTo($pathToFile))
            ->will($this->returnValue($returnArray));

        //work with objects - Each model persist
        $em->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$this->isInstanceOf('\RentJeeves\DataBundle\Entity\AciCollectPayUserProfile')],
                [$this->isInstanceOf('\RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling')],
                [$this->isInstanceOf('\RentJeeves\DataBundle\Entity\AciCollectPayUserProfile')],
                [$this->isInstanceOf('\RentJeeves\DataBundle\Entity\AciCollectPayProfileBilling')]
            );

        $importer = new CsvImporter($em, $deserializer, $validator);
        $importer->import('/1.csv', $holding);

        $errors = $importer->getErrors();
        $this->assertTrue(empty($errors));
    }

    /**
     * @return \Doctrine\ORM\EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEmMock()
    {
        return $this->getMock('\Doctrine\ORM\EntityManager', [], [], '', false);
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
     * @return EnrollmentResponseFileDeserializer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDeserializerMock()
    {
        return $this->getMock(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer\EnrollmentResponseFileDeserializer',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\AciImportProfileMapRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAciMapRepositoryMock()
    {
        return $this->getMock(
            '\RentJeeves\DataBundle\Entity\AciImportProfileMapRepository',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContractRepositoryMock()
    {
        return $this->getMock(
            '\RentJeeves\DataBundle\Entity\ContractRepository',
            [],
            [],
            '',
            false
        );
    }
}
