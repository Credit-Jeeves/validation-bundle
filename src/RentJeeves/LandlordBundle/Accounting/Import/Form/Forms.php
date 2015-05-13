<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Form;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Form\ImportContractFinishType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;

/**
 * @property ModelImport currentImportModel
 */
trait Forms
{

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
     *
     * @return Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * @param bool $isUseToken
     *
     * @return Form
     */
    public function getContractForm($isUseToken = true)
    {
        return $this->createForm(
            new ImportContractType(
                $this->em,
                $this->translator,
                $this->currentImportModel,
                $isUseToken,
                $isMultipleProperty = $this->storage->isMultipleProperty()
            )
        );
    }

    /**
     * @return Form
     */
    public function getCreateUserAndCreateContractForm()
    {
        return $this->createForm(
            new ImportNewUserWithContractType(
                $this->em,
                $this->translator,
                $this->currentImportModel,
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
     * @return null|Form
     */
    protected function getForm()
    {
        $tenant   = $this->currentImportModel->getTenant();
        $contract = $this->currentImportModel->getContract();
        $tenantId   = $tenant->getId();
        $contractId = $contract->getId();

        //Update contract or Create contract with exist User
        if (($tenantId &&
                in_array(
                    $contract->getStatus(),
                    [
                        ContractStatus::INVITE,
                        ContractStatus::APPROVED,
                        ContractStatus::CURRENT
                    ]
                )
                && $contractId)
            || ($tenantId && empty($contractId))
            || $hasContractWaiting = $this->currentImportModel->getHasContractWaiting()
        ) {
            $form = $this->getContractForm($isUseToken = true);
            $form->setData($contract);
            $form->get('residentMapping')->setData($this->currentImportModel->getResidentMapping());
            if ($this->storage->isMultipleProperty()) {
                $form->get('unitMapping')->setData($this->currentImportModel->getUnitMapping());
            }

            return $form;
        }

        //Create contract and create user
        if (empty($tenantId) &&
            $contract->getStatus() === ContractStatus::INVITE &&
            empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm();
            $form->get('tenant')->setData($tenant);
            $form->get('contract')->setData($contract);
            $form->get('contract')->get('residentMapping')->setData($this->currentImportModel->getResidentMapping());
            if ($this->storage->isMultipleProperty()) {
                $form->get('contract')->get('unitMapping')->setData($this->currentImportModel->getUnitMapping());
            }

            return $form;
        }

        //Finish exist contract form
        if ($contract->getStatus() === ContractStatus::FINISHED && !$this->currentImportModel->isSkipped()) {
            $form = $this->getContractFinishForm();
            $form->setData($contract);

            return $form;
        }

        return null;
    }
}
