<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use CreditJeeves\DataBundle\Entity\Group as GroupEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageInterface as ImportStorage;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
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
use Monolog\Logger;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;

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
     * @Inject("%support_email%")
     * @var string
     */
    public $supportEmail;

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
     * @Inject("logger")
     * @var Logger
     */
    public $logger;

    /**
     * @Inject("fp_badaboom.exception_catcher")
     * @var ExceptionCatcher
     */
    public $exceptionCatcher;

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
     * @var boolean
     */
    protected $isNeedSendInvite = false;

    /**
     * @var ModelImport
     */
    protected $currentImportModel;

    /**
     * @var ArrayCollection
     */
    protected $collectionImportModel;

    public function __construct()
    {
        $this->currentImportModel = new ModelImport();
    }

    /**
     * @param string $field
     * @return \DateTime|null
     */
    public function getDateByField($field)
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
            $this->currentImportModel->setIsValidDateFormat(false);
            return null;
        }

        $formattedMonth = (int) $date->format('m');
        $formattedDay = (int) $date->format('j');
        $formattedYear = (int) $date->format('Y');

        if ($formattedMonth < 1 || $formattedMonth > 12) {
            $this->currentImportModel->setIsValidDateFormat(false);
            return null;
        }

        if ($formattedDay < 1 || $formattedDay > 31) {
            $this->currentImportModel->setIsValidDateFormat(false);
            return null;
        }

        if ($formattedYear < 1900 || $formattedYear > 2250) {
            $this->currentImportModel->setIsValidDateFormat(false);
            return null;
        }

        return ($date) ? $date : null;
    }

    /**
     * @param $postData
     *
     * @return bool
     */
    protected function isValidNotEditedFields($postData)
    {
        $unit = $this->currentImportModel->getContract()->getUnit();
        if (!empty($unit) && !$isSingle = $this->getIsSingle($postData)) {
            $errors = $this->validator->validate($unit, array("import"));
            if (count($errors) > 0) {
                return false;
            }
        }

        $residentMapping = $this->currentImportModel->getResidentMapping();
        $errors = $this->validator->validate($residentMapping, array("import"));
        if (count($errors) > 0 || $this->isUsedResidentId($residentMapping)) {
            return false;
        }

        if ($this->storage->isMultipleProperty()) {
            $unitMapping = $this->currentImportModel->getUnitMapping();
            $errors = $this->validator->validate($unitMapping, array("import"));
            if (count($errors) > 0) {
                return false;
            }
        }

        $property = $this->currentImportModel->getContract()->getProperty();
        if (!$property || !$property->getNumber()) {
            return false;
        }

        if ($this->storage->isMultipleProperty()) {
            $isSingle = false;
            if (isset($postData['contract']['isSingle']) || isset($postData['isSingle'])) {
                $isSingle = true;
            }

            if (!$property->isAllowedToSetSingle($isSingle, $this->group->getId())) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * Create model objects for imported row
     *
     * @param array $row
     * @param integer $lineNumber
     *
     * @return ModelImport
     */
    protected function createCurrentImportModel(array $row, $lineNumber)
    {
        $this->currentImportModel = new ModelImport();
        $this->currentImportModel->setHandler($this);
        $this->currentImportModel->setNumber($lineNumber);
        $this->setTenant($row);
        $this->currentImportModel->setEmail($row[ImportMapping::KEY_EMAIL]);
        $this->group = $this->getGroup($row);
        $this->currentImportModel->setIsSkipped(false);

        if (!$this->group) {
            $this->currentImportModel->setIsSkipped(true);
            $this->currentImportModel->setSkippedMessage($this->translator->trans('import.error.empty_group'));
        }

        if ($this->group && !$this->group->getGroupSettings()->getIsIntegrated()) {
            $this->currentImportModel->setIsSkipped(true);
            $this->currentImportModel->setSkippedMessage(
                $this->translator->trans(
                    'import.error.group_not_integrated',
                    ['%group_name%' => $this->group->getName()]
                )
            );
        }

        if ($this->mapping->isSkipped($row)) {
            $this->currentImportModel->setIsSkipped(true);
            $this->currentImportModel->setSkippedMessage(
                $this->translator->trans('import.info.skipped1')
            );
        }

        $this->setContract($row);

        if ($this->currentImportModel->getContract() &&
            !$property = $this->currentImportModel->getContract()->getProperty()
        ) {
            $this->currentImportModel->setAddress($row[ImportMapping::KEY_STREET].','.$row[ImportMapping::KEY_CITY]);
        }

        $token = (!$this->isCreateCsrfToken) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        $this->currentImportModel->setCsrfToken($token);

        $this->setResident($row);

        if ($this->currentImportModel->getContract() && $unit = $this->currentImportModel->getContract()->getUnit()) {
            $this->currentImportModel->setUnitMapping($this->getUnitMapping($row, $unit));
        } else {
            $this->currentImportModel->setUnitMapping(new UnitMapping());
        }

        $contractWaiting = $this->getContractWaiting();
        if ($contractWaiting->getId() && !$this->currentImportModel->getContract()->getId()) {
            $this->currentImportModel->setHasContractWaiting(true);
            $this->currentImportModel->setContractWaiting($contractWaiting);
            $this->currentImportModel->getTenant()->setFirstName($contractWaiting->getFirstName());
            $this->currentImportModel->getTenant()->setLastName($contractWaiting->getLastName());
        } elseif ($this->currentImportModel->getContract()->getId() && $contractWaiting->getId()) {
            $this->em->remove($contractWaiting);
            $this->currentImportModel->setHasContractWaiting(false);
        }

        if (!$this->currentImportModel->getIsSkipped() &&
            is_null($this->currentImportModel->getContract()->getId()) &&
            $this->isContractInPast()
        ) {
            $this->currentImportModel->setIsSkipped(true);
            $this->currentImportModel->setSkippedMessage(
                $this->translator->trans('import.info.skipped2')
            );
        }

        if (!$this->currentImportModel->getIsSkipped() && $form = $this->getForm($this->currentImportModel)) {
            $this->currentImportModel->setForm($form);
        }

        $this->setErrors();
    }

    protected function setErrors()
    {
        $errors[$this->currentImportModel->getNumber()] = array();
        $form = $this->currentImportModel->getForm();
        $lineNumber = $this->currentImportModel->getNumber();
        if (!$this->isCreateCsrfToken && !$this->currentImportModel->getIsSkipped()) {
            $errors = $this->runFormValidation($form, $lineNumber, $this->currentImportModel->getCsrfToken());
            if ($this->isUsedResidentId($this->currentImportModel->getResidentMapping())) {
                $errors[$lineNumber][uniqid()][ImportMapping::KEY_RESIDENT_ID] = $this->translator
                    ->trans(
                        'error.residentId.already_use',
                        array(
                            '%email%'   => $this->getEmailByResident($import->getResidentMapping()->getResidentId()),
                            '%support_email%' => $this->supportEmail
                        )
                    );
            }
            $this->currentImportModel->setErrors($errors);
        }

        if (isset($this->userEmails[$this->currentImportModel->getTenant()->getEmail()]) &&
            $this->userEmails[$this->currentImportModel->getTenant()->getEmail()] > 1
        ) {
            $errors[$this->currentImportModel->getNumber()][uniqid()]['tenant_email'] =
                $this->translator->trans(
                    'import.user.already_used'
                );
            $this->currentImportModel->setIsSkipped(true);
        }

        $unit = $this->currentImportModel->getContract()->getUnit();
        $existUnitMapping = ($unit) ? $unit->getUnitMapping() : null;
        $unitMappingImported = $this->currentImportModel->getUnitMapping();

        if ($existUnitMapping &&
            !is_null($unitMappingImported->getExternalUnitId()) &&
            $existUnitMapping->getExternalUnitId() !== $unitMappingImported->getExternalUnitId()
        ) {
            $errors[$this->currentImportModel->getNumber()]
                [uniqid()]
                ['import_new_user_with_contract_contract_unitMapping_externalUnitId'] =
                    $this->translator->trans(
                        'import.unit_mapping.already_used'
                    );
            $this->currentImportModel->setIsSkipped(true);
        }

        $this->currentImportModel->setErrors($errors);
    }

    /**
     * @param $form
     * @param $lineNumber
     * @param null $token
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
    public function initCollectionImportModel()
    {
        $data       = $this->mapping->getData($this->storage->getOffsetStart(), $rowCount = self::ROW_ON_PAGE);
        $this->collectionImportModel = new ArrayCollection(array());

        foreach ($data as $key => $values) {
            try {
                $this->createCurrentImportModel($values, $key);
                $this->currentImportModel->setNumber($key);
            } catch (Exception $e) {
                $this->manageException($e);
            }
            $this->collectionImportModel->add(clone $this->currentImportModel);
        }
        $this->clearResidentData();
    }

    /**
     * @return ArrayCollection
     */
    public function getCurrentCollectionImportModel()
    {
        if (empty($this->collectionImportModel)) {
            $this->initCollectionImportModel();
        }

        return $this->collectionImportModel;
    }

    /**
     *
     * Return array of errors for this data if don't have errors, then save.
     *
     * @param array $data
     *
     * @return array
     */
    public function saveForms(array $data)
    {
        $this->isCreateCsrfToken = true;

        $errors     = [];
        $lines      = [];
        $errorsNotEditableFields = [];

        foreach ($data as $postData) {
            $postData['line'] = (int)$postData['line'];

            /** @var $import Import */
            foreach ($this->getCurrentCollectionImportModel() as $keyCollection => $import) {
                if ($import->getNumber() === $postData['line']) {
                    $lineNumber = $postData['line'];
                    $lines[] = $lineNumber;
                    $this->currentImportModel = $import;

                    // Validate data which get from client by post request
                    $resultBind = $this->bindForm($postData, $errors);

                    if (!isset($errors[$lineNumber]) &&
                        !$resultBind &&
                        !is_null($form = $import->getForm())
                    ) {
                        $errorsNotEditableFields[$lineNumber] = $this->runFormValidation(
                            $form,
                            $lineNumber
                        )[$lineNumber];

                        continue;
                    }

                    $isException = $this->currentImportModel->getUniqueKeyException();

                    if (!empty($isException)) {
                        continue;
                    }

                    if (!empty($errors[$lineNumber])) {
                        continue;
                    }

                    if (!$resultBind) {
                        continue;
                    }

                    if ($this->tryToSaveRow($lineNumber)) {
                        $this->getCurrentCollectionImportModel()->remove($keyCollection);
                    }
                }
            }
        }

        /** @var $import ModelImport */
        foreach ($this->getCurrentCollectionImportModel() as $keyCollection => $import) {
            $token = $import->getCsrfToken();
            if (empty($token)) {
                $token = $this->formCsrfProvider->generateCsrfToken($import->getNumber());
                $import->setCsrfToken($token);
            }

            if (isset($errors[$import->getNumber()])) {
                continue;
            }

            if ($import->getUniqueKeyException()) {
                continue;
            }

            $this->getCurrentCollectionImportModel()->remove($keyCollection);
        }

        $this->isCreateCsrfToken = false;

        return $errors + $errorsNotEditableFields;
    }

    protected function tryToSaveRow($lineNumber)
    {
        try {
            $this->em->flush();
        } catch (Exception $e) {
            $this->manageException($e);
            return false;
        }

        $this->sendInviteEmail();
        $this->removeToken($lineNumber);

        return true;
    }

    /**
     * @param Exception $e
     */
    public function manageException(Exception $e)
    {
        $uniqueKeyException = uniqid();
        $exception = new ImportHandlerException($e);
        $exception->setUniqueKey($uniqueKeyException);
        $this->currentImportModel->setUniqueKeyException($uniqueKeyException);
        $this->exceptionCatcher->handleException($exception);
        $messageForLogging = sprintf(
            'Exception message: %s, in File: %s, In Line: %s',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $this->logger->addCritical($messageForLogging);

        if (!$this->currentImportModel->getResidentMapping()) {
            $this->currentImportModel->setResidentMapping(new ResidentMapping());
        }
        if (!$this->currentImportModel->getTenant()) {
            $this->currentImportModel->setTenant(new EntityTenant());
        }
        if (!$this->currentImportModel->getContract()) {
            $this->currentImportModel->setContract(new EntityContract());
        }
        if (!$this->currentImportModel->getUnitMapping()) {
            $this->currentImportModel->setUnitMapping(new UnitMapping());
        }
    }

    /**
     * @param $csrfToken
     */
    protected function removeToken($csrfToken)
    {
        $tokenManager = $this->formCsrfProvider->getTokenManager();
        $tokenManager->removeToken($csrfToken);
    }

    /**
     * Send email from $isNeedSendInvite
     */
    protected function sendInviteEmail()
    {
        if (!$this->currentImportModel->getContract()->getTenant()->getEmail() || !$this->isNeedSendInvite) {
            return;
        }
        /** @var $contract EntityContract */
        $contract = $this->currentImportModel->getContract();
        $result = $this->mailer->sendRjTenantInvite(
            $contract->getTenant(),
            $this->user,
            $contract,
            $isImported = "1"
        );
        $this->isNeedSendInvite = false;
        if ($result === false) {
            $message = sprintf("Can't send invite email to user %s", $contract->getTenant()->getEmail());
            throw new ImportHandlerException($message);
        }
    }
}
