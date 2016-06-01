<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Services;

use Doctrine\ORM\NonUniqueResultException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Services\ContractMergingProcessor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ContractMergingProcessorCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnNullIfContractStatusNotAllowedForLookingDuplicate()
    {
        $mergingProcessor = new ContractMergingProcessor($this->getEntityManagerMock(), $this->getLoggerMock());
        $duplicateContract = $mergingProcessor->getOneOrNullDuplicate(
            $this->getContractMock(ContractStatus::APPROVED),
            'test@email.com',
            'residentId'
        );
        $this->assertNull(
            $duplicateContract,
            'Should return "null" if contract status is not "invite", "waiting" or "pending"'
        );
    }

    /**
     * @test
     */
    public function shouldReturnNullIfParametersForLookingContractAreEmpty()
    {
        $mergingProcessor = new ContractMergingProcessor($this->getEntityManagerMock(), $this->getLoggerMock());
        $duplicateContract = $mergingProcessor->getOneOrNullDuplicate($this->getContractMock());
        $this->assertNull(
            $duplicateContract,
            'Should return "null" if contract status is not "invite", "waiting" or "pending"'
        );
    }


    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Found more then one duplicate contracts
     */
    public function shouldThrowExceptionIfFoundMoreThenOneContract()
    {
        $contractRepositoryMock = $this->getContractRepositoryMock();
        $contractRepositoryMock
            ->method('getOneOrNullDuplicateContractByEmail')
            ->willThrowException(new NonUniqueResultException());

        $entityManagerMock = $this->getEntityManagerMock();
        $entityManagerMock->method('getRepository')->willReturn($contractRepositoryMock);

        $mergingProcessor = new ContractMergingProcessor($entityManagerMock, $this->getLoggerMock());
        $mergingProcessor->getOneOrNullDuplicate($this->getContractMock(), 'test@email.com');
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Found different duplicate contracts by both parameters
     */
    public function shouldThrowExceptionIfFoundDifferentContractsByBothParameters()
    {
        $contractRepositoryMock = $this->getContractRepositoryMock();
        $contractRepositoryMock
            ->method('getOneOrNullDuplicateContractByResidentId')
            ->willReturn($this->getContractMock(ContractStatus::PENDING, true, 1));
        $contractRepositoryMock
            ->method('getOneOrNullDuplicateContractByEmail')
            ->willReturn($this->getContractMock(ContractStatus::PENDING, false, 2));

        $entityManagerMock = $this->getEntityManagerMock();
        $entityManagerMock->method('getRepository')->willReturn($contractRepositoryMock);

        $mergingProcessor = new ContractMergingProcessor($entityManagerMock, $this->getLoggerMock());
        $mergingProcessor->getOneOrNullDuplicate($this->getContractMock(), 'test@email.com', 'residentId');
    }

    /**
     * @test
     */
    public function shouldNotLookingDuplicateContractByResidentIdIfIsNotAllowedEditResidentId()
    {
        $contractRepositoryMock = $this->getContractRepositoryMock();
        $contractRepositoryMock
            ->expects($this->never())
            ->method('getOneOrNullDuplicateContractByResidentId');

        $entityManagerMock = $this->getEntityManagerMock();
        $entityManagerMock->method('getRepository')->willReturn($contractRepositoryMock);

        $mergingProcessor = new ContractMergingProcessor($entityManagerMock, $this->getLoggerMock());
        $duplicateContract = $mergingProcessor->getOneOrNullDuplicate(
            $this->getContractMock(ContractStatus::WAITING, false),
            null,
            'residentId'
        );
        $this->assertNull(
            $duplicateContract,
            'Should return "null" if nothing found'
        );
    }

    /**
     * @test
     */
    public function shouldReturnFoundDuplicateContract()
    {
        $contractRepositoryMock = $this->getContractRepositoryMock();

        $contractRepositoryMock
            ->method('getOneOrNullDuplicateContractByEmail')
            ->willReturn($this->getContractMock(ContractStatus::PENDING, true, 1));

        $entityManagerMock = $this->getEntityManagerMock();
        $entityManagerMock->method('getRepository')->willReturn($contractRepositoryMock);

        $mergingProcessor = new ContractMergingProcessor($entityManagerMock, $this->getLoggerMock());
        $duplicateContract = $mergingProcessor->getOneOrNullDuplicate(
            $this->getContractMock(ContractStatus::WAITING, true, 2),
            'test@email.com'
        );
        $this->assertNotEmpty($duplicateContract, 'Should return found duplicate contract');
        $this->assertEquals($duplicateContract->getId(), 1, 'Should return found duplicate contract, that was mocked');
    }

    /**
     * @return ContractRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContractRepositoryMock()
    {
        return $this->getBaseMock('RentJeeves\DataBundle\Entity\ContractRepository');
    }

    /**
     * @param string $status
     * @param bool $isAllowedEditResidentId
     * @param int $id
     * @return \PHPUnit_Framework_MockObject_MockObject|Contract
     */
    protected function getContractMock($status = ContractStatus::INVITE, $isAllowedEditResidentId = true, $id = 1)
    {
        $group = $this->getBaseMock('CreditJeeves\DataBundle\Entity\Group');
        $group->method('isAllowedEditResidentId')->willReturn($isAllowedEditResidentId);

        $contract = $this->getBaseMock('RentJeeves\DataBundle\Entity\Contract');
        $contract->method('getStatus')->willReturn($status);
        $contract->method('getGroup')->willReturn($group);
        $contract->method('getId')->willReturn($id);

        return $contract;
    }
}
