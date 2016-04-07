<?php

namespace RentJeeves\CoreBundle\Tests\ContractManagement;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\CoreBundle\ContractManagement\ContractCreator;
use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\Exception\UserCreatorException;
use RentJeeves\CoreBundle\UserManagement\UserCreator;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Component\Validator\ConstraintViolationList;

class ContractManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractManagerException
     * @expectedExceptionMessage Can`t create new contract without email and firstName and lastName
     */
    public function shouldThrowExceptionIfInputDataDoesntContainRequiredFieldsForCreateContract()
    {
        $contractManager = new ContractManager(
            $this->getBaseMock(ContractCreator::class),
            $this->getBaseMock(UserCreator::class),
            $this->getEntityManagerMock(),
            $this->getLoggerMock(),
            $this->getValidatorMock(),
            $this->getMailerMock()
        );
        $contractManager->createContract(new Unit(), new ContractDTO());
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Exception\ContractManagerException
     * @expectedExceptionMessage UserCreatorExceptionTest
     */
    public function shouldThrowExceptionAndDoRollbackIfCreatorThrowExceptionForCreateContract()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('rollback');
        $em->expects($this->never())
            ->method('commit');

        $userCreator = $this->getBaseMock(UserCreator::class);
        $userCreator->expects($this->once())
            ->method('createTenant')
            ->willThrowException(new UserCreatorException('UserCreatorExceptionTest'));
        $contractManager = new ContractManager(
            $this->getBaseMock(ContractCreator::class),
            $userCreator,
            $em,
            $this->getLoggerMock(),
            $this->getValidatorMock(),
            $this->getMailerMock()
        );
        $contractDTO = new ContractDTO();
        $contractDTO->setEmail('test@test.com');
        $contractManager->createContract(new Unit(), $contractDTO);
    }

    /**
     * @test
     */
    public function shouldCallCommitIfAllEntitiesAreCreatedCorrectlyForCreateContract()
    {
        $tenant = new Tenant();
        $holding = new Holding();

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->never())
            ->method('rollback');
        $em->expects($this->once())
            ->method('commit');
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($subject) use ($holding, $tenant) {
                /** @var ResidentMapping $subject */
                $this->assertInstanceOf(ResidentMapping::class, $subject, 'Unexpected object for persist.');
                $this->assertEquals($holding, $subject->getHolding(), 'Incorrect Holding for ResidentMapping.');
                $this->assertEquals($tenant, $subject->getTenant(), 'Incorrect Tenant for ResidentMapping.');
                $this->assertEquals('test', $subject->getResidentId(), 'Incorrect ResidentId for ResidentMapping.');

                return true;
            }));

        $userCreator = $this->getBaseMock(UserCreator::class);
        $userCreator->expects($this->once())
            ->method('createTenant')
            ->willReturn($tenant);

        $contract = new Contract();
        $contract->setHolding($holding);

        $contractCreator = $this->getBaseMock(ContractCreator::class);
        $contractCreator->expects($this->once())
            ->method('createContract')
            ->willReturn($contract);
        $contractManager = new ContractManager(
            $contractCreator,
            $userCreator,
            $em,
            $this->getLoggerMock(),
            $this->getValidatorMock(),
            $this->getMailerMock()
        );
        $contractDTO = new ContractDTO();
        $contractDTO->setEmail('test@test.com');
        $contractDTO->setExternalResidentId('test');
        $contract = $contractManager->createContract(new Unit(), $contractDTO);

        $this->assertInstanceOf(Contract::class, $contract, 'createContract should return instance of Contract class');
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldThrowExceptionIfInputContractHasNotWaitingStatusForMoveContractOutOfWaiting()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->never())
            ->method('flush');
        $contractManager = new ContractManager(
            $this->getBaseMock(ContractCreator::class),
            $this->getBaseMock(UserCreator::class),
            $em,
            $this->getLoggerMock(),
            $this->getValidatorMock(),
            $this->getMailerMock()
        );

        $contract = new Contract();
        $contract->setStatus(ContractStatus::APPROVED);

        $contractManager->moveContractOutOfWaiting($contract);
    }

    /**
     * @test
     */
    public function shouldOnlyUpdateStatusIfEmailIsEmptyForMoveContractOutOfWaiting()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('flush');
        $contractManager = new ContractManager(
            $this->getBaseMock(ContractCreator::class),
            $this->getBaseMock(UserCreator::class),
            $em,
            $this->getLoggerMock(),
            $this->getValidatorMock(),
            $this->getMailerMock()
        );

        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);

        $contractManager->moveContractOutOfWaiting($contract);

        $this->assertEquals(ContractStatus::APPROVED, $contract->getStatus(), 'Not correct status after move.');
    }

    /**
     * @test
     */
    public function shouldUpdateStatusAndEmailForTenantAndSendEmailIfEmailIsNotEmptyForMoveContractOutOfWaiting()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('flush');
        $mailer = $this->getMailerMock();
        $mailer->expects($this->once())
            ->method('sendRjTenantInvite');
        $validator = $this->getValidatorMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());
        $contractManager = new ContractManager(
            $this->getBaseMock(ContractCreator::class),
            $this->getBaseMock(UserCreator::class),
            $em,
            $this->getLoggerMock(),
            $validator,
            $mailer
        );

        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);
        $contract->setTenant(new Tenant());

        $holding = new Holding();
        $this->writeAttribute($holding, 'users', new ArrayCollection([new Landlord()]));
        $contract->setHolding($holding);

        $contractManager->moveContractOutOfWaiting($contract, ContractStatus::CURRENT, 'test@test.com');

        $this->assertEquals(ContractStatus::CURRENT, $contract->getStatus(), 'Not correct status after move.');
        $this->assertNotNull($contract->getTenant()->getEmail(), 'Email is not updated.');
        $this->assertTrue($contract->getTenant()->getEmailNotification(), 'EmailNotification is not updated.');
    }
}
