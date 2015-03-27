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
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract;
use Exception;
use Doctrine\ORM\UnitOfWork;

/**
 * @property ModelImport currentImportModel
 * @property HandlerAbstract collectionImportModel
 * @property ModelImport isNeedSendInvite
 * @property HandlerAbstract contractProcess
 * @method HandlerAbstract manageException
 */
trait FormBind
{
    /**
     * Return array of errors and persisting entity, also fill $isNeedSendInvite if needed
     *
     * @param ModelImport $import
     * @param $postData
     * @param array &$errors
     *
     * @return boolean
     */
    protected function bindForm($postData, &$errors)
    {
        $form = $this->currentImportModel->getForm();
        $line = $postData['line'];
        unset($postData['line']);

        self::prepareSubmit($postData);


        if ($this->currentImportModel->getIsSkipped() || $this->getIsSkip($postData)) {
            $this->collectionImportModel->removeElement($this->currentImportModel);
            $this->detach();
            return false;
        }

        if (!$this->isValidNotEditedFields($postData)) {
            return false;
        }

        if (!$form) {
            return false;
        }

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
                    $this->bindImportContractForm($form);
                    break;
                case 'import_new_user_with_contract':
                    $this->bindImportNewUserWithContractForm($form);
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
    protected function bindImportContractForm(Form $form)
    {
        /**
         * @var $contract Contract
         */
        $contract = $form->getData();

        if ($this->currentImportModel->getHasContractWaiting()) {
            $sendInvite = $form->get('sendInvite')->getNormData();
            $this->processingContractWaiting(
                $this->currentImportModel->getTenant(),
                $contract,
                $this->currentImportModel->getResidentMapping(),
                $sendInvite
            );
            return;
        }

        if ($this->storage->isMultipleProperty()) {
            $isSingle = $form->get('isSingle')->getData();
            $this->afterBindForm($isSingle);
            $unitMapping = $form->get('unitMapping')->getData();
            if (!$unitMapping->getUnit()) {
                $unitMapping->setUnit($contract->getUnit());
            }
            $this->em->persist($unitMapping);
        }

        if (!$contract->getId()) {
            $this->isNeedSendInvite = true;
        }

        $residentMapping = $form->get('residentMapping')->getData();
        $this->em->persist($residentMapping);
    }

    /**
     * @param Form $form
     * @param ModelImport $import
     */
    protected function bindImportNewUserWithContractForm(Form $form)
    {
        $data = $form->getData();
        $tenant = $data['tenant'];
        /** @var $contract Contract  */
        $contract = $data['contract'];
        if ($this->storage->isMultipleProperty()) {
            $isSingle = $form->get('contract')->get('isSingle')->getData();
            $this->afterBindForm($isSingle);
            $unitMapping = $form->get('contract')->get('unitMapping')->getData();
            if (!$unitMapping->getUnit()) {
                $unitMapping->setUnit($contract->getUnit());
            }
            $this->em->persist($unitMapping);
        }
        $email = $tenant->getEmail();
        $residentMapping = $form->get('contract')->get('residentMapping')->getData();
        if (empty($email)) {
            $waitingContract = $this->getContractWaiting();
            $this->em->persist($waitingContract);
        } else {
            $this->em->persist($residentMapping);
        }

        if (isset($data['sendInvite']) && $data['sendInvite']) {
            $this->isNeedSendInvite = true;
        }
    }

    /**
     * @param Tenant $tenant
     * @param Contract $contract
     * @param ResidentMapping $residentMapping
     * @param boolean $sendInvite
     */
    protected function processingContractWaiting($sendInvite)
    {
        /**
         * @var $waitingContract ContractWaiting
         */
        $waitingContract = $this->getContractWaiting();

        if ($this->currentImportModel->getTenant()->getEmail()) {
            //Remove contract because we get duplicate contract
            $this->currentImportModel->getTenant()->removeContract($this->currentImportModel->getContract());
            $this->em->persist($this->currentImportModel->getTenant());
            $contract = $this->contractProcess->createContractFromWaiting(
                $this->currentImportModel->getTenant(),
                $waitingContract
            );
            $contract->setStatus(ContractStatus::INVITE);
            $this->em->persist($contract);
            if ($sendInvite) {
                $this->isNeedSendInvite = true;
            }
        } else {
            $this->em->persist($waitingContract);
        }
    }

    public function processingOperationAndOrder(Operation $operation)
    {
        $tenant = $this->currentImportModel->getTenant();
        $contract = $this->currentImportModel->getContract();

        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setType(OrderType::CASH);
        $order->setUser($tenant);
        $order->setSum($operation->getAmount());

        $operation->setContract($contract);
        $operation->setOrder($order);

        $this->em->persist($order);
    }

    /**
     * Use only for multiple property
     *
     * @param Contract $contract
     * @param $isSingle
     */
    protected function afterBindForm($isSingle)
    {
        try {
            $contract = $this->currentImportModel->getContract();
            if ($contract->getGroup()) {
                $property = $contract->getProperty();
                $property->addPropertyGroup($contract->getGroup());
                $contract->getGroup()->addGroupProperty($property);
                $this->em->flush($contract->getGroup());

                if ($isSingle && !$contract->getUnit()) {
                    $unit = $this->propertyProcess->setupSingleProperty($property, ['doFlush' => false]);
                    $contract->setUnit($unit);
                    $this->em->flush($unit);
                }
                $this->em->flush($property);
            } else {
                throw new RuntimeException("no group for contract");
            }
        } catch (Exception $e) {
            $this->manageException($e);
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

    protected function detach()
    {
        if (!$this->currentImportModel->getForm()) {
            return;
        }

        $contract = $this->currentImportModel->getContract();
        if ($this->isPersisted($contract)) {
            $this->em->detach($contract);
        }
        $unit = $contract->getUnit();
        if ($this->isPersisted($unit)) {
            $this->em->detach($unit);
        }
        $tenant = $this->currentImportModel->getTenant();
        if ($this->isPersisted($tenant) && $this->userEmails[$tenant->getEmail()] === 1) {
            $this->em->detach($tenant);
        }

        $residentMapping = $this->currentImportModel->getResidentMapping();
        if ($this->isPersisted($residentMapping) && !$residentMapping->getId()) {
            $this->em->detach($residentMapping);
        }
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isPersisted($entity)
    {
        return in_array(
            $this->em->getUnitOfWork()->getEntityState($entity),
            [UnitOfWork::STATE_MANAGED, UnitOfWork::STATE_NEW]
        );
    }

    /**
     * We need remove form name from key of array and leave just name form field
     * it's need for form submit
     */
    public static function prepareSubmit(&$formData)
    {
        foreach ($formData as $key => $value) {
            preg_match('/^[A-Za_z\_]{1,}+[\[]{1,1}/i', $key, $matches);
            if (!isset($matches[0])) {
                continue;
            }
            $newKey = preg_replace('/^[A-Za_z\_]{1,}/i', '', $key);
            $newKey = substr($newKey, 1);
            $formData[$newKey] = $value;
            unset($formData[$key]);
        }
    }
}
