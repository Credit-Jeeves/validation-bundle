<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group as GroupEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageInterface as ImportStorage;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\CoreBundle\DateTime;
use \Exception;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\Validator\Validator;
use RentJeeves\LandlordBundle\Accounting\Import\Form\Forms;
use RentJeeves\LandlordBundle\Accounting\Import\Form\FormBind;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Contract;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Operation;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Property;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Resident;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Tenant;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Unit;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\LandlordBundle\Accounting\Import\Traits\OnlyReviewNewTenantsAndExceptionsTrait;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Group;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
abstract class HandlerAbstract implements HandlerInterface
{
    use Forms;
    use Contract;
    use Tenant;
    use Resident;
    use Property;
    use FormBind;
    use Unit;
    use OnlyReviewNewTenantsAndExceptionsTrait;
    use Operation;
    use Group;
    use FormErrors;

    const ROW_ON_PAGE = 9;

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
     * @var SessionUser
     */
    protected $user;

    /**
     * @var GroupEntity
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
    public function getDateByField(Import $import, $field)
    {
        if (empty($field)) {
            return null;
        }

        try {
            $date = DateTime::createFromFormat($this->storage->getDateFormat(), $field);
        } catch (Exception $e) {
            return null;
        }

        $errors = DateTime::getLastErrors();

        if (!empty($errors['warning_count']) || !empty($errors['errors'])) {
            $import->setIsValidDateFormat(false);
            return null;
        }

        $formattedMonth = (int) $date->format('m');
        $formattedDay = (int) $date->format('j');
        $formattedYear = (int) $date->format('Y');

        if ($formattedMonth < 1 || $formattedMonth > 12) {
            $import->setIsValidDateFormat(false);
            return null;
        }

        if ($formattedDay < 1 || $formattedDay > 31) {
            $import->setIsValidDateFormat(false);
            return null;
        }

        if ($formattedYear < 1900 || $formattedYear > 2250) {
            $import->setIsValidDateFormat(false);
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
        $import->setHandler($this);
        $import->setNumber($lineNumber);
        $tenant = $this->getTenant($row);
        $import->setEmail($row[ImportMapping::KEY_EMAIL]);
        $this->group = $this->getGroup($row);
        $import->setTenant($tenant);
        $import->setIsSkipped(false);

        if (!$this->group) {
            $import->setIsSkipped(true);
            $import->setSkippedMessage($this->translator->trans('import.error.empty_group'));
        }

        if ($this->group && !$this->group->getGroupSettings()->getIsIntegrated()) {
            $import->setIsSkipped(true);
            $import->setSkippedMessage(
                $this->translator->trans(
                    'import.error.group_not_integrated',
                    ['%group_name%' => $this->group->getName()]
                )
            );
        }

        if ($this->mapping->isSkipped($row)) {
            $import->setIsSkipped(true);
            $import->setSkippedMessage(
                $this->translator->trans('import.info.skipped1')
            );
        }

        $import->setContract($contract = $this->getContract($import, $row));

        if ($contract && !$property = $contract->getProperty()) {
            $import->setAddress($row[ImportMapping::KEY_STREET].','.$row[ImportMapping::KEY_CITY]);
        }

        $token = (!$this->isCreateCsrfToken) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        $import->setCsrfToken($token);

        $import->setResidentMapping($this->getResident($tenant, $row));

        if ($contract && $unit = $contract->getUnit()) {
            $import->setUnitMapping($this->getUnitMapping($row, $unit));
        }

        $contractWaiting = $this->getContractWaiting(
            $import->getTenant(),
            $import->getContract(),
            $import->getResidentMapping()
        );

        if ($contractWaiting->getId() && !$import->getContract()->getId()) {
            $import->setHasContractWaiting(true);
            $import->setContractWaiting($contractWaiting);
            $tenant->setFirstName($contractWaiting->getFirstName());
            $tenant->setLastName($contractWaiting->getLastName());
        } elseif ($contract->getId() && $contractWaiting->getId()) {
            $this->em->remove($contractWaiting);
            $import->setHasContractWaiting(false);
        }

        if (!$import->getIsSkipped() &&
            is_null($contract->getId()) &&
            $this->contractInPast($contract)
        ) {
            $import->setIsSkipped(true);
            $import->setSkippedMessage(
                $this->translator->trans('import.info.skipped2')
            );
        }

        if (!$import->getIsSkipped() && $form = $this->getForm($import)) {
            $import->setForm($form);
        }

        $this->setErrors($import);

        return $import;
    }

    /**
     * @param Import $import
     */
    protected function setErrors(Import $import)
    {
        $errors[$import->getNumber()] = array();
        $form = $import->getForm();
        $lineNumber = $import->getNumber();
        if (!$this->isCreateCsrfToken && !$import->getIsSkipped()) {
            $errors = $this->runFormValidation($form, $lineNumber, $import->getCsrfToken());
            if ($this->isUsedResidentId($import->getResidentMapping())) {
                $errors[$lineNumber][uniqid()][ImportMapping::KEY_RESIDENT_ID] = $this->translator
                    ->trans(
                        'error.residentId.already_use'
                    );
            }
            $import->setErrors($errors);
        }

        if (isset($this->userEmails[$import->getTenant()->getEmail()]) &&
            $this->userEmails[$import->getTenant()->getEmail()] > 1
        ) {
            $errors[$import->getNumber()][uniqid()]['tenant_email'] =
                $this->translator->trans(
                    'import.user.already_used'
                );
            $import->setIsSkipped(true);
        }

        $unit = $import->getContract()->getUnit();
        $existUnitMapping = ($unit) ? $unit->getUnitMapping() : null;
        $unitMappingImported = $import->getUnitMapping();

        if ($existUnitMapping &&
            !is_null($unitMappingImported->getExternalUnitId()) &&
            $existUnitMapping->getExternalUnitId() !== $unitMappingImported->getExternalUnitId()
        ) {
            $errors[$import->getNumber()]
                [uniqid()]
                ['import_new_user_with_contract_contract_unitMapping_externalUnitId'] =
                    $this->translator->trans(
                        'import.unit_mapping.already_used'
                    );
            $import->setIsSkipped(true);
        }

        $import->setErrors($errors);
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
            $result = $this->mailer->sendRjTenantInvite(
                $contract->getTenant(),
                $this->user,
                $contract,
                $isImported = "1"
            );

            if ($result === false) {
                $message = sprintf("Can't send invite email to user %s", $contract->getTenant()->getEmail());
                throw new ImportHandlerException($message);
            }
        }

        $this->emailSendingQueue = array();
    }
}
