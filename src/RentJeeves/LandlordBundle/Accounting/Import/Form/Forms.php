<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Form;

use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Form\ImportContractFinishType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
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
        $this->logger->debug('Getting form to update or create contract with existing user');

        return $this->createForm(
            new ImportContractType(
                $this->em,
                $this->translator,
                $this->currentImportModel,
                $isUseToken,
                $isMultipleProperty = $this->storage->isMultipleProperty(),
                true,
                $this->isSupportResidentId()
            )
        );
    }

    /**
     * @return Form
     */
    public function getCreateUserAndCreateContractForm()
    {
        $this->logger->debug('Getting form to create contract and user');

        return $this->createForm(
            new ImportNewUserWithContractType(
                $this->em,
                $this->translator,
                $this->currentImportModel,
                $isMultipleProperty = $this->storage->isMultipleProperty(),
                $this->isSupportResidentId()
            )
        );
    }

    /**
     * @return Form
     */
    public function getContractFinishForm()
    {
        $this->logger->debug('Getting form to finish existing contract');

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
        $contractStatus = $contract->getStatus();
        $isSkipped = $this->currentImportModel->isSkipped();

        $this->logger->debug(
            sprintf(
                "getForm: tId:'%s', cId:'%s', cStatus:'%s', skip:%s",
                $tenantId,
                $contractId,
                $contractStatus,
                ($isSkipped) ? "true" : "false"
            )
        );

        //Update contract or Create contract with exist User
        if (($tenantId &&
                in_array(
                    $contractStatus,
                    [
                        ContractStatus::INVITE,
                        ContractStatus::APPROVED,
                        ContractStatus::CURRENT,
                        ContractStatus::WAITING
                    ]
                )
                && $contractId)
            || ($tenantId && empty($contractId))
            || empty($tenant->getEmail())
        ) {
            $form = $this->getContractForm($isUseToken = true);
            $form->setData($contract);
            if ($this->isSupportResidentId()) {
                $form->get('residentMapping')->setData($this->currentImportModel->getResidentMapping());
            }
            if ($this->storage->isMultipleProperty()) {
                $form->get('unitMapping')->setData($this->currentImportModel->getUnitMapping());
            }

            return $form;
        }

        //Create contract and create user
        if (empty($tenantId) &&
            $contractStatus === ContractStatus::INVITE &&
            empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm();
            $form->get('tenant')->setData($tenant);
            $form->get('contract')->setData($contract);
            if ($this->isSupportResidentId()) {
                $form->get('contract')->get('residentMapping')->setData(
                    $this->currentImportModel->getResidentMapping()
                );
            }

            if ($this->storage->isMultipleProperty()) {
                $form->get('contract')->get('unitMapping')->setData($this->currentImportModel->getUnitMapping());
            }

            return $form;
        }

        //Finish exist contract form
        if ($contractStatus === ContractStatus::FINISHED && !$isSkipped) {
            $form = $this->getContractFinishForm();
            $form->setData($contract);

            return $form;
        }

        return null;
    }
}
