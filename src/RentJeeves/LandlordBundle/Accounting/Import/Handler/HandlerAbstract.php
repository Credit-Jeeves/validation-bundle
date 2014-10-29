<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\CoreBundle\DateTime;
use \Exception;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\Validator\Validator;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use JMS\DiExtraBundle\Annotation\Inject;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
abstract class HandlerAbstract implements HandlerInterface
{
    const ROW_ON_PAGE = 9;

    use FormErrors;

    /**
     * @Inject("doctrine.orm.default_entity_manager")
     * @var EntityManager
     */
    public $em;

    /**
     * @Inject("form.factory")
     * @var
     */
    public $formFactory;

    /**
     * @Inject("form.csrf_provider")
     * @var CsrfTokenManagerAdapter
     */
    public $formCsrfProvider;

    /**
     * @Inject("project.mailer")
     * @var Mailer
     */
    public $mailer;

    /**
     * @Inject("validator")
     * @var Validator
     */
    public $validator;

    /**
     * @Inject("%kernel.default_locale%")
     * @var string
     */
    public $locale;

    /**
     * @Inject("property.process")
     */
    public $propertyProcess;

    /**
     * @Inject("contract.process")
     * @var ContractProcess
     */
    public $contractProcess;

    /**
     * @var Landlord
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var ImportMapping
     */
    protected $mapping;

    /**
     * @var ImportStorage
     */
    protected $storage;

    /**
     * @var bool
     */
    protected $isCreateCsrfToken = false;

    /**
     * @var array
     */
    protected $emailSendingQueue = array();



    /**
     * @param $field
     * @return \DateTime|null
     */
    public function getDateByField($field)
    {
        try {
            $date = DateTime::createFromFormat($this->storage->getDateFormat(), $field);
        } catch (Exception $e) {
            return null;
        }

        $errors = DateTime::getLastErrors();

        if (!empty($errors['warning_count']) || !empty($errors['errors'])) {
            return null;
        }

        return ($date) ? $date : null;
    }

    /**
     * @param Import $import
     * @param $postData
     *
     * @return bool
     */
    protected function isValidNotEditedFields(ModelImport $import, $postData)
    {
        $unit = $import->getContract()->getUnit();
        if (!empty($unit) && !$isSingle = $this->getIsSingle($postData)) {
            $errors = $this->validator->validate($unit, array("import"));
            if (count($errors) > 0) {
                return false;
            }
        }

        $residentMapping = $import->getResidentMapping();
        $errors = $this->validator->validate($residentMapping, array("import"));
        if (count($errors) > 0 || $this->isUsedResidentId($residentMapping)) {
            return false;
        }

        if ($this->storage->isMultipleProperty()) {
            $unitMapping = $import->getUnitMapping();
            $errors = $this->validator->validate($unitMapping, array("import"));
            if (count($errors) > 0) {
                return false;
            }
        }

        $property = $import->getContract()->getProperty();
        if (!$property || !$property->getNumber()) {
            return false;
        }

        if ($this->storage->isMultipleProperty()) {
            $isSingle = false;
            if (isset($postData['contract']['isSingle']) || isset($postData['isSingle'])) {
                $isSingle = true;
            }

            if (!$property->isAllowedToBeSingle($isSingle, $this->group->getId())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $row
     * @param integer $lineNumber
     *
     * @return ModelImport
     */
    protected function getImport(array $row, $lineNumber)
    {
        $import = new ModelImport();
        $tenant = $this->getTenant($row);
        $import->setEmail($row[Mapping::KEY_EMAIL]);
        $import->setTenant($tenant);
        $import->setIsSkipped(false);

        if ($this->mapping->isSkipped($row)) {
            $import->setIsSkipped(true);
        }

        $import->setContract($contract = $this->getContract($import, $row));

        if ($contract && !$property = $contract->getProperty()) {
            $import->setAddress($row[Mapping::KEY_STREET].','.$row[Mapping::KEY_CITY]);
        }

        $token      = (!$this->isCreateCsrfToken) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        $import->setCsrfToken($token);

        if ($operation = $this->getOperation($import, $row)) {
            $import->setOperation($operation);
        }

        $import->setResidentMapping($this->getResident($tenant, $row));
        $import->setUnitMapping($this->getUnitMapping($row));

        $contractWaiting = $this->getContractWaiting(
            $import->getTenant(),
            $import->getContract(),
            $import->getResidentMapping()
        );

        if ($contractWaiting->getId() && !$import->getContract()->getId()) {
            $import->setHasContractWaiting(true);
            $tenant->setFirstName($contractWaiting->getFirstName());
            $tenant->setLastName($contractWaiting->getLastName());
        } elseif ($contract->getId() && $contractWaiting->getId()) {
            $this->em->remove($contractWaiting);
            $import->setHasContractWaiting(false);
            $this->em->flush();
        }

        if (!$import->getIsSkipped() &&
            is_null($contract->getId()) &&
            $this->contractInPast($contract)
        ) {
            $import->setIsSkipped(true);
        }

        if (!$import->getIsSkipped() && $form = $this->getForm($import)) {
            $import->setForm($form);
        }

        if (!$this->isCreateCsrfToken && !$import->getIsSkipped()) {
            $errors = $this->runFormValidation($form, $lineNumber, $token);
            if ($this->isUsedResidentId($import->getResidentMapping())) {
                $errors[$lineNumber][$form->getName()][Mapping::KEY_RESIDENT_ID] = $this->translator
                    ->trans(
                        'error.residentId.already_use'
                    );
            }
            $import->setErrors($errors);
        }


        return $import;
    }

    /**
     * @param $form
     * @param $lineNumber
     *
     * @return array
     */
    protected function runFormValidation($form, $lineNumber, $token = null)
    {
        $viewForm = $form->createView();
        $submittedData = $this->getSubmittedDataFromForm($viewForm->children);
        if (!is_null($token)) {
            $submittedData['_token'] = $token;
        }

        $form->submit($submittedData);

        if (!$form->isValid()) {
            return array(
                $lineNumber => $this->getFormErrors($form)
            );
        }

        return array($lineNumber => array());
    }

    /**
     * This method get field from form and create array from it, which we can use
     * for $form->submit($data); And after that we can run validation.
     *
     * @param array $children
     * @return array
     */
    protected function getSubmittedDataFromForm(array $children)
    {
        $submittedData = array();
        foreach ($children as $fieldName => $data) {
            if (!empty($data->children)) {
                $submittedData[$data->vars['name']] = $this->getSubmittedDataFromForm($data->children);
                continue;
            }
            $submittedData[$data->vars['name']] = $data->vars['value'];
        }

        return $submittedData;
    }

    /**
     * @return ArrayCollection
     */
    public function getImportModelCollection()
    {
        $data       = $this->mapping->getData($this->storage->getOffsetStart(), $rowCount = self::ROW_ON_PAGE);
        $collection = new ArrayCollection(array());

        foreach ($data as $key => $values) {
            $import = $this->getImport($values, $key);
            $import->setNumber($key);
            $collection->add($import);
        }
        $this->clearResidentIds();
        return $collection;
    }

    /**
     *
     * Return array of errors for this data if don't have errors -> saved
     *
     * @param array $data
     *
     * @return array
     */
    public function saveForms(array $data)
    {
        $this->isCreateCsrfToken = true;

        $mappedData = $this->getImportModelCollection();
        $errors     = array();
        $lines      = array();
        $errorsNotEditableFields = array();

        foreach ($data as $formData) {
            $formData['line'] = (int)$formData['line'];

            /**
             * @var $import Import
             */
            foreach ($mappedData as $key => $import) {
                if ($import->getNumber() === $formData['line']) {
                    $currentLine = $formData['line'];
                    $lines[] = $currentLine;
                    $resultBind = $this->bindForm($import, $formData, $errors);

                    if (!isset($errors[$currentLine]) &&
                        !$resultBind &&
                        !is_null($form = $import->getForm())
                    ) {
                        $errorsNotEditableFields[$currentLine] = $this->runFormValidation(
                            $form,
                            $currentLine
                        )[$currentLine];
                    }
                }
            }
        }
        $this->isCreateCsrfToken = false;

        if (empty($errors)) {
            $this->em->flush();
            $this->clearTokens($lines);
            $this->sendInviteEmail();

            return array();
        }

        if (empty($errorsNotEditableFields)) {
            return $errors;
        }

        return $errors + $errorsNotEditableFields;
    }

    /**
     * Remove csrf tokens, which we generate for current rows
     *
     * @param array $lines
     */
    protected function clearTokens(array $lines)
    {
        $tokenManager = $this->formCsrfProvider->getTokenManager();
        foreach ($lines as $line) {
            $tokenManager->removeToken($line);
        }
    }

    /**
     * Send email from emailSendingQueue
     */
    protected function sendInviteEmail()
    {
        if (empty($this->emailSendingQueue)) {
            return;
        }
        /**
         * @var $contract EntityContract
         */
        foreach ($this->emailSendingQueue as $contract) {
            $this->mailer->sendRjTenantInvite(
                $contract->getTenant(),
                $this->user,
                $contract,
                $isImported = "1"
            );
        }

        $this->emailSendingQueue = array();
    }
}
