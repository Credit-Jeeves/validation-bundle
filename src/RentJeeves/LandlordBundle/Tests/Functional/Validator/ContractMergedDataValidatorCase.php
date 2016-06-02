<?php

namespace RentJeeves\LandlordBundle\Tests\Functional\Validator;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\LandlordBundle\MergingContracts\ContractMergedDTO;
use RentJeeves\LandlordBundle\Validator\ContractMergedData;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ContractMergedDataValidatorCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function shouldTrowExceptionIfValueNotContractMergedDTO()
    {
        $contractMergedDataConstraint = new ContractMergedData();
        $this->getContainer()->get('validator')->validateValue('string', $contractMergedDataConstraint);
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenGivenDataIsValid()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();

        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(0, $validationErrors, 'Should not be added any errors');
    }

    /**
     * @test
     */
    public function shouldAddErrorIfFoundUserWithInvalidType()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setTenantEmail('landlord1@example.com');
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'user.error.type.invalid',
            $validationErrors->get(0)->getMessage(),
            'Should be added error with invalid user type'
        );
    }

    /**
     * @test
     */
    public function shouldAddErrorIfFoundTenantDoesNotHaveMergingContract()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setTenantEmail('tenant11@example.com');
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'contract.error.email.exist',
            $validationErrors->get(0)->getMessage(),
            'Should be added error that user exists'
        );
    }

    /**
     * @test
     */
    public function shouldAddErrorIfPropertyIsInvalid()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setContractPropertyId(0);
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'contract.merging.error.property.invalid',
            $validationErrors->get(0)->getMessage(),
            'Should be added error that property is invalid'
        );
    }

    /**
     * @test
     */
    public function shouldAddErrorIfUnitIsInvalid()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setContractUnitId(33);
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'contract.merging.error.unit.invalid',
            $validationErrors->get(0)->getMessage(),
            'Should be added error that property is invalid'
        );
    }

    /**
     * @test
     */
    public function shouldNotAddErrorIfUnitIsEmptyForSingleProperty()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setContractPropertyId(19);
        $mergingModel->setContractUnitId(0);
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(0, $validationErrors, 'Should not be added any errors');
    }

    /**
     * @test
     */
    public function shouldAddErrorIfResidentIdIsEmptyForIsAllowedEditResidentId()
    {
        $this->load(true);
        $mergingModel = $this->getValidData();
        $mergingModel->setContractResidentId(null);
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'contract.merging.error.resident.empty',
            $validationErrors->get(0)->getMessage(),
            'Should be added error that resident id is empty'
        );
    }

    /**
     * @test
     */
    public function shouldAddErrorIfLeaseIdIsEmptyForIsAllowedEditLeaseId()
    {
        $this->load(true);
        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 26);
        $contract->getHolding()->setAccountingSystem(reset(AccountingSystem::$allowedEditLeaseId));
        $this->getEntityManager()->flush();
        $mergingModel = $this->getValidData();
        $mergingModel->setContractLeaseId(null);
        $contractMergedDataConstraint = new ContractMergedData();
        $validationErrors = $this->getContainer()
            ->get('validator')
            ->validateValue($mergingModel, $contractMergedDataConstraint);
        $this->assertCount(1, $validationErrors, 'Should be added just one error');
        $this->assertEquals(
            'contract.merging.error.lease.empty',
            $validationErrors->get(0)->getMessage(),
            'Should be added error that lease id is empty'
        );
    }

    /**
     * @return ContractMergedDTO
     */
    protected function getValidData()
    {
        /** @var ContractMergedDTO $mergingModel */
        $mergingModel = $this->getContainer()
            ->get('jms_serializer')
            ->deserialize(
                [
                    'email' => 'treewolta.j@example.com',
                    'propertyId' => 1,
                    'unitId' => 39,
                    'residentId' => 'test_resident_1',
                ],
                'RentJeeves\LandlordBundle\MergingContracts\ContractMergedDTO',
                'array'
            );

        $originalContract = $this->getEntityManager()->find('RjDataBundle:Contract', 26);
        $duplicateContract = $this->getEntityManager()->find('RjDataBundle:Contract', 25);

        $mergingModel->setOriginalContract($originalContract);
        $mergingModel->setDuplicateContract($duplicateContract);

        return $mergingModel;
    }
}
