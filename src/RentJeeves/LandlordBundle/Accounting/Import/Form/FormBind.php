<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Form;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use Symfony\Component\Form\Form;
use RentJeeves\CoreBundle\DateTime;

trait FormBind
{
    /**
     * Return array of errors and persisting entity, also fill emailSendingQueue if needed
     *
     * @param ModelImport $import
     * @param $postData
     * @param array &$errors
     *
     * @return boolean
     */
    protected function bindForm(ModelImport $import, $postData, &$errors)
    {
        $form = $import->getForm();
        $line = $postData['line'];
        unset($postData['line']);

        if (!$form) {
            return false;
        }

        self::prepareSubmit($postData, $form->getName(), $import);

        if (!$this->isValidNotEditedFields($import, $postData)) {
            return false;
        }

        if ($import->getIsSkipped() || $this->getIsSkip($postData)) {
            $this->detach($import);
            return false;
        }

        $form = $import->getForm();
        if (!isset($postData['_token'])) {
            return false;
        }

        $form->submit($postData);
        $isCsrfTokenValid = $this->formCsrfProvider->isCsrfTokenValid($line, $postData['_token']);

        if ($form->isValid() && $isCsrfTokenValid) {
            //Do save and maybe in future move it to factory pattern, when have more logic
            switch ($form->getName()) {
                case 'import_contract_finish':
                    $contract = $form->getData();
                    $this->em->persist($contract);
                    break;
                case 'import_contract':
                    $this->bindImportContractForm($form, $import);
                    break;
                case 'import_new_user_with_contract':
                    $this->bindImportNewUserWithContractForm($form, $import);
                    break;
            }

            return true;
        }

        $errors[$line] = $this->getFormErrors($form);
        if (!$isCsrfTokenValid) {
            $errors[$line]['_global'] = $this->translator->trans('csrf.token.is.invalid');
        }

        return false;
    }

    /**
     * @param Form $form
     * @param ModelImport $import
     */
    protected function bindImportContractForm(Form $form, ModelImport $import)
    {
        /**
         * @var $contract Contract
         */
        $contract = $form->getData();

        if ($import->getHasContractWaiting()) {
            $sendInvite = $form->get('sendInvite')->getNormData();
            $this->processingContractWaiting(
                $import->getTenant(),
                $contract,
                $import->getResidentMapping(),
                $sendInvite
            );
            return;
        }

        if ($this->storage->isMultipleProperty()) {
            $isSingle = $form->get('isSingle')->getData();
            $this->afterBindForm($contract, $isSingle);
            $unitMapping = $form->get('unitMapping')->getData();
            if (!$unitMapping->getUnit()) {
                $unitMapping->setUnit($contract->getUnit());
            }
            $this->em->persist($unitMapping);
        }

        if (!$contract->getId()) {
            $this->emailSendingQueue[] = $contract;
        }

        $residentMapping = $form->get('residentMapping')->getData();
        $this->em->persist($residentMapping);
    }

    /**
     * @param Form $form
     * @param ModelImport $import
     */
    protected function bindImportNewUserWithContractForm(Form $form, ModelImport $import)
    {
        $data = $form->getData();
        $tenant = $data['tenant'];
        /**
         * @var $contract Contract
         */
        $contract = $data['contract'];
        if ($this->storage->isMultipleProperty()) {
            $isSingle = $form->get('contract')->get('isSingle')->getData();
            $this->afterBindForm($contract, $isSingle);
            $unitMapping = $form->get('contract')->get('unitMapping')->getData();
            if (!$unitMapping->getUnit()) {
                $unitMapping->setUnit($contract->getUnit());
            }
            $this->em->persist($unitMapping);
        }
        $email = $tenant->getEmail();
        $residentMapping = $form->get('contract')->get('residentMapping')->getData();
        if (empty($email)) {
            $waitingContract = $this->getContractWaiting($tenant, $contract, $residentMapping);
            $this->em->persist($waitingContract);
        } else {
            $this->em->persist($residentMapping);
            $this->processingContract($import, $contract);
            if ($data['sendInvite']) {
                $this->emailSendingQueue[] = $contract;
            }
        }
    }

    /**
     * @param Tenant $tenant
     * @param Contract $contract
     * @param ResidentMapping $residentMapping
     * @param boolean $sendInvite
     */
    protected function processingContractWaiting(
        Tenant $tenant,
        Contract $contract,
        ResidentMapping $residentMapping,
        $sendInvite
    ) {
        /**
         * @var $waitingContract ContractWaiting
         */
        $waitingContract = $this->getContractWaiting(
            $tenant,
            $contract,
            $residentMapping
        );

        if ($tenant->getEmail()) {
            $tenant->removeContract($contract); //Remove contract because we get duplicate contract
            $this->em->persist($tenant);
            $contract = $this->contractProcess->createContractFromWaiting($tenant, $waitingContract);
            $contract->setStatus(ContractStatus::INVITE);
            $this->em->persist($contract);
            if ($sendInvite) {
                $this->emailSendingQueue[] = $contract;
            }
        } else {
            $this->em->persist($waitingContract);
        }
    }

    /**
     * @param Tenant $tenant
     * @param Operation $operation
     * @param Contract $contract
     */
    public function processingOperationAndOrder(Tenant $tenant, Operation $operation, Contract $contract)
    {
        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setType(OrderType::CASH);
        $order->setUser($tenant);
        $order->addOperation($operation);
        $order->setSum($operation->getAmount());

        $operation->setContract($contract);
        $operation->setOrder($order);
        $contract->addOperation($operation);
    }

    /**
     * Use only for multiple property
     *
     * @param Contract $contract
     * @param $isSingle
     */
    protected function afterBindForm(Contract $contract, $isSingle)
    {
        $property = $contract->getProperty();
        $property->setIsSingle($isSingle);
        $property->addPropertyGroup($this->group);
        $this->group->addGroupProperty($property);
        $this->em->flush($this->group);
        $this->em->flush($property);

        if ($property->isSingle() && !$contract->getUnit()) {
            $contract->setUnit($property->getSingleUnit());
        }
    }

    /**
     * @TODO Need find out better way because if we add more form with such data but different structure
     * we need modify this method.
     * Variant for better:
     * add interface for form with method to get skip value value by form?
     *
     * @param $postData
     * @return bool
     */
    protected function getIsSkip($postData)
    {
        if (isset($postData['contract']['skip']) || isset($postData['skip'])) {
            return true;
        }

        return false;
    }

    /**
     * @TODO Need find out better way because if we add more form with such data but different structure
     * we need modify this method.
     * Variant for better:
     * add interface for form with method to get isSingle value by form?
     *
     * @param $postData
     * @return bool
     */
    protected function getIsSingle($postData)
    {
        if (!$this->storage->isMultipleProperty()) {
            return false;
        }

        if (isset($postData['contract']['isSingle']) || isset($postData['isSingle'])) {
            return true;
        }

        return false;
    }

    /**
     * @param ModelImport $import
     */
    protected function detach(ModelImport $import)
    {
        $contract = $import->getContract();
        if ($contract->getId()) {
            $this->em->detach($contract);
        }
        $unit = $contract->getUnit();
        if ($unit && $unit->getId()) {
            $this->em->detach($unit);
        }
        $tenant = $import->getTenant();
        if ($tenant->getId() && $this->userEmails[$tenant->getEmail()] === 1) {
            $this->em->detach($tenant);
        }
        $residentMapping = $import->getResidentMapping();
        if ($residentMapping->getId()) {
            $this->em->detach($residentMapping);
        }
        $operation = $import->getOperation();
        if ($operation && $operation->getId()) {
            $this->em->detach($operation);
        }
    }

    /**
     * We need remove form name from key of array and leave just name form field
     * it's need for form submit
     */
    public static function prepareSubmit(&$formData, $formName, ModelImport $import)
    {
        $length = strlen($formName) + 1;
        foreach ($formData as $key => $value) {
            if (!preg_match('/' . $formName . '\[/', $key)) {
                continue;
            }
            $newKey            = substr($key, $length, strlen($key) - 1);
            $formData[$newKey] = $value;
            unset($formData[$key]);
        }
    }
}
