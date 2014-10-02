<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Form\ImportContractFinishType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use Symfony\Component\Form\FormFactory;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;

trait ImportForms
{

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type The built type of the form
     * @param mixed $data The initial data for the form
     * @param array $options Options for the form
     *
     * @return Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * @param Tenant $tenant
     * @param ResidentMapping $residentMapping
     * @param UnitMapping $unitMapping
     * @param Unit $unit
     * @param bool $isUseToken
     * @param bool $isUseOperation
     *
     * @return Form
     */
    public function getContractForm(
        Tenant $tenant,
        ResidentMapping $residentMapping,
        UnitMapping $unitMapping,
        Unit $unit = null,
        $isUseToken = true,
        $isUseOperation = true
    ) {
        return $this->createForm(
            new ImportContractType(
                $this->em,
                $this->translator,
                $tenant,
                $residentMapping,
                $unitMapping,
                $unit,
                $isUseToken,
                $isUseOperation,
                $isMultipleProperty = $this->storage->isMultipleProperty()
            )
        );
    }

    /**
     * @param ResidentMapping $residentMapping
     * @param UnitMapping $unitMapping
     * @param Unit $unit
     *
     * @return Form
     */
    public function getCreateUserAndCreateContractForm(
        ResidentMapping $residentMapping,
        UnitMapping $unitMapping,
        Unit $unit = null
    ) {
        return $this->createForm(
            new ImportNewUserWithContractType(
                $this->em,
                $this->translator,
                $residentMapping,
                $unitMapping,
                new Tenant(),
                $unit,
                $isMultipleProperty = $this->storage->isMultipleProperty()
            )
        );
    }

    /**
     * @return Form
     */
    public function getContractFinishForm()
    {
        return $this->createForm(new ImportContractFinishType());
    }

    /**
     * Creating form for particular import
     *
     * @param Import $import
     *
     * @return null|Form
     */
    protected function getForm(ModelImport $import)
    {
        $tenant   = $import->getTenant();
        $contract = $import->getContract();
        $operation = $import->getOperation();
        $residentMapping = $import->getResidentMapping();
        $unitMapping = $import->getUnitMapping();

        $tenantId   = $tenant->getId();
        $contractId = $contract->getId();

        //Update contract or Create contract with exist User
        if (($tenantId &&
                in_array(
                    $contract->getStatus(),
                    array(
                        ContractStatus::INVITE,
                        ContractStatus::APPROVED,
                        ContractStatus::CURRENT
                    )
                )
                && $contractId)
            || ($tenantId && empty($contractId))
            || $hasContractWaiting = $import->getHasContractWaiting()
        ) {
            $isUseOperation = ($import->getOperation() === null || $import->getHasContractWaiting())? false : true;
            $form = $this->getContractForm(
                $tenant,
                $residentMapping,
                $unitMapping,
                $contract->getUnit(),
                $isUseToken = true,
                $isUseOperation
            );
            $form->setData($contract);
            if ($operation instanceof Operation) {
                $form->get('operation')->setData($operation);
            }
            $form->get('residentMapping')->setData($import->getResidentMapping());
            if ($this->storage->isMultipleProperty()) {
                $form->get('unitMapping')->setData($import->getUnitMapping());
            }
            return $form;
        }

        //Create contract and create user
        if (empty($tenantId) &&
            $contract->getStatus() === ContractStatus::INVITE &&
            empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm(
                $residentMapping,
                $unitMapping,
                $contract->getUnit()
            );
            $form->get('tenant')->setData($tenant);
            $form->get('contract')->setData($contract);
            if ($operation instanceof Operation) {
                $form->get('contract')->get('operation')->setData($operation);
            }
            $form->get('contract')->get('residentMapping')->setData($import->getResidentMapping());
            if ($this->storage->isMultipleProperty()) {
                $form->get('contract')->get('unitMapping')->setData($import->getUnitMapping());
            }
            return $form;
        }

        //Finish exist contract form
        if ($contract->getStatus() === ContractStatus::FINISHED && !$import->getIsSkipped()) {
            $form = $this->getContractFinishForm();
            $form->setData($contract);

            return $form;
        }

        return null;
    }
}
