<?php

namespace RentJeeves\LandlordBundle\Accounting;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ComponentBundle\Service\Google;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Exception\ImportProcessException;
use RentJeeves\LandlordBundle\Form\ImportContractFinishType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;
use Symfony\Component\Form\Form;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\CoreBundle\DateTime;
use \Exception;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Validator;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.process")
 */
class ImportProcess
{
    use FormErrors;

    const ROW_ON_PAGE = 10;

    /**
     * @var array
     */
    protected $usedResidentsIds = array();

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Landlord
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var CsrfTokenManagerAdapter
     */
    protected $formCsrfProvider;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ImportMapping
     */
    protected $mapping;

    /**
     * @var ImportStorage
     */
    protected $storage;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $isCreateCsrfToken = false;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    protected $emailSendingQueue = array();

    /**
     * @var Google
     */
    protected $googleService;

    /**
     * @var ContractProcess
     */
    protected $contractProcess;

    /**
     * @InjectParams({
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "translator"       = @Inject("translator"),
     *     "sessionUser"      = @Inject("core.session.landlord"),
     *     "formFactory"      = @Inject("form.factory"),
     *     "translator"       = @Inject("translator"),
     *     "formCsrfProvider" = @Inject("form.csrf_provider"),
     *     "mailer"           = @Inject("project.mailer"),
     *     "storage"          = @Inject("accounting.import.storage"),
     *     "mapping"          = @Inject("accounting.import.mapping"),
     *     "validator"        = @Inject("validator"),
     *     "googleService"    = @Inject("google"),
     *     "contractProcess"  = @Inject("contract.process"),
     *     "locale"           = @Inject("%kernel.default_locale%")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        SessionUser $sessionUser,
        FormFactory $formFactory,
        Translator $translator,
        CsrfTokenManagerAdapter $formCsrfProvider,
        Mailer $mailer,
        ImportStorage $storage,
        ImportMapping $mapping,
        Validator $validator,
        Google $googleService,
        ContractProcess $contractProcess,
        $locale
    ) {
        $this->em               = $em;
        $this->user             = $sessionUser->getUser();
        $this->group            = $sessionUser->getGroup();
        $this->formFactory      = $formFactory;
        $this->translator       = $translator;
        $this->formCsrfProvider = $formCsrfProvider;
        $this->mailer           = $mailer;
        $this->mapping          = $mapping;
        $this->storage          = $storage;
        $this->validator        = $validator;
        $this->locale           = $locale;
        $this->googleService    = $googleService;
        $this->contractProcess  = $contractProcess;
    }

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
     * @return DateTime|null
     */
    public function getDateByField($field)
    {
        try {
            $date = DateTime::createFromFormat($this->storage->getDateFormat(), $field);
        } catch (Exception $e) {
            return null;
        }

        return ($date) ? $date : null;
    }

    /**
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
     * @param $row
     * @param $tenant
     *
     * @return Contract
     */
    protected function createContract(array $row, Tenant $tenant)
    {
        $contract = new Contract();
        if ($tenant->getId()) {
            $contract->setStatus(ContractStatus::APPROVED);
        } else {
            $contract->setStatus(ContractStatus::INVITE);
        }

        $property = $this->getProperty($row);
        if ($property) {
            $contract->setProperty($property);
        }
        $contract->setGroup($this->group);
        $contract->setHolding($this->group->getHolding());
        $contract->setTenant($tenant);
        if ($unit = $this->getUnit($row, $contract->getProperty())) {
            $contract->setUnit($unit);
        }
        $contract->setDueDate($this->group->getGroupSettings()->getDueDate());

        /**
         * If we don't have unit and property don't have flag is_single set it to single by default
         */
        if (empty($row[ImportMapping::KEY_UNIT]) && $property && is_null($property->getIsSingle())) {
            $property->setIsSingle(true);
        }

        $tenant->addContract($contract);

        return $contract;
    }

    /**
     * @param string $residentId
     */
    protected function addResidentId($residentId)
    {
        if (!isset($this->usedResidentsIds[$residentId])) {
            $this->usedResidentsIds[$residentId] = 1;
        } else {
            $this->usedResidentsIds[$residentId]++;
        }
    }

    protected function clearResidentIds()
    {
        $this->usedResidentsIds = array();
    }

    /**
     * @param ResidentMapping $residentMapping
     * @return bool
     */
    public function isUsedResidentId(ResidentMapping $residentMapping)
    {
        $id = $residentMapping->getResidentId();
        return (isset($this->usedResidentsIds[$id]) && $this->usedResidentsIds[$id] > 1)? true : false;
    }

    /**
     * @param Tenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    protected function createResident(Tenant $tenant, array $row)
    {
        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($this->user->getHolding());
        $residentMapping->setResidentId($row[ImportMapping::KEY_RESIDENT_ID]);
        $this->addResidentId($row[ImportMapping::KEY_RESIDENT_ID]);

        return $residentMapping;
    }

    /**
     * @param Tenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    public function getResident(Tenant $tenant, array $row)
    {
        if (is_null($tenant->getId())) {
            return $this->createResident($tenant, $row);
        }

        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'tenant'        => $tenant->getId(),
                'holding'       => $this->user->getHolding()->getId(),
                'residentId'    => $row[ImportMapping::KEY_RESIDENT_ID],
            )
        );

        if (empty($residentMapping)) {
            return $this->createResident($tenant, $row);
        }

        return $residentMapping;
    }

    public function getUnitMapping(array $row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return new UnitMapping();
        }

        $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            array(
                'externalUnitId' => $row[ImportMapping::KEY_UNIT_ID],
            )
        );
        if (!$unitMapping) {
            $unitMapping = new UnitMapping();
            $unitMapping->setExternalUnitId($row[ImportMapping::KEY_UNIT_ID]);
        }

        return $unitMapping;
    }

    /**
     * @param $row
     *
     * @return Unit
     */
    protected function getUnit(array $row, Property $property = null)
    {
        if (is_null($property)) {
            return null;
        }
        if ($property->isSingle()) {
            return $property->getSingleUnit();
        }

        if (empty($row[ImportMapping::KEY_UNIT])) {
            return null;
        }

        $params = array(
            'name' => $row[ImportMapping::KEY_UNIT],
        );

        if ($this->group) {
            $params['group'] = $this->group;
        }

        if ($this->storage->isMultipleProperty() && !is_null($property)) {
            $params['property'] = $property->getId();
        } elseif ($this->storage->getPropertyId()) {
            $params['property'] = $this->storage->getPropertyId();
        }

        if ($holding = $this->group->getHolding()) {
            $params['holding'] = $holding;
        }

        if (!empty($params['name']) && !empty($params['property'])) {
            $unit = $this->em->getRepository('RjDataBundle:Unit')->findOneBy($params);
        }

        if (!empty($unit)) {
            return $unit;
        }

        $unit = new Unit();
        $unit->setName($row[ImportMapping::KEY_UNIT]);
        if ($property) {
            $unit->setProperty($property);
        }
        $unit->setHolding($this->group->getHolding());
        $unit->setGroup($this->group);

        return $unit;
    }

    /**
     * @param Import $import
     * @param $row
     *
     * @return Contract
     */
    protected function getContract(ModelImport $import, array $row)
    {
        $tenant = $import->getTenant();

        if (!$tenant->getId()) {
            $contract = $this->createContract($row, $tenant);
        } else {
            $contract = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                $row[ImportMapping::KEY_UNIT],
                isset($row[ImportMapping::KEY_UNIT_ID])? $row[ImportMapping::KEY_UNIT_ID] : null
            );

            if (empty($contract)) {
                $contract = $this->createContract($row, $tenant);
            }
        }

        //set data from csv file
        $contract->setIntegratedBalance($row[ImportMapping::KEY_BALANCE]);
        $contract->setRent($row[ImportMapping::KEY_RENT]);

        $moveIn = $this->getDateByField($row[ImportMapping::KEY_MOVE_IN]);
        $today = new DateTime();
        if ($moveIn > $today) {
            $startAt = $moveIn;
        } else {
            $groupDueDate = $this->group->getGroupSettings()->getDueDate();
            $startAt = new DateTime();
            if ($row[ImportMapping::KEY_BALANCE] <= 0) {
                // snap to next month due date (default from group) for start_at
                $startAt->modify('+1 month');
                $startAt = $startAt->setDate(null, null, $groupDueDate);
            } else {
                // snap to this month due date (default from group) for start_at
                $startAt = $startAt->setDate(null, null, $groupDueDate);
            }
        }

        $contract->setStartAt($startAt);
        if (isset($row[ImportMapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[ImportMapping::KEY_MONTH_TO_MONTH] == 'Y')
        ) {
            $contract->setFinishAt(null);
        } else {
            $contract->setFinishAt($this->getDateByField($row[ImportMapping::KEY_LEASE_END]));
        }

        if (!empty($row[ImportMapping::KEY_MOVE_OUT])) {
            $import->setMoveOut($this->getDateByField($row[ImportMapping::KEY_MOVE_OUT]));
            $contract->setStatus(ContractStatus::FINISHED);
        }

        return $contract;
    }

    /**
     * @param Import $import
     * @param $row
     *
     * @return Operation|null
     */
    protected function getOperation(ModelImport $import, array $row)
    {
        if (!$this->mapping->hasPaymentMapping($row)) {
            return null;
        }

        $contract = $import->getContract();
        if ($contract->getStatus() !== ContractStatus::CURRENT) {
            return null;
        }

        $tenant = $import->getTenant();
        $amount = $row[ImportMapping::KEY_PAYMENT_AMOUNT];
        $paidFor = $this->getDateByField($row[ImportMapping::KEY_PAYMENT_DATE]);

        if ($paidFor instanceof DateTime && $amount > 0) {
            $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
                $tenant,
                $contract,
                $paidFor,
                $amount
            );

            //We can't create double payment for current month
            if ($operation) {
                return null;
            }
        }

        $operation = new Operation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($amount);
        $operation->setType(OperationType::RENT);

        return $operation;
    }

    /**
     * @param array $row
     *
     * @return Tenant
     */
    protected function getTenant(array $row)
    {
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImportWithResident(
            $row[ImportMapping::KEY_EMAIL],
            $row[ImportMapping::KEY_RESIDENT_ID],
            $this->user->getHolding()->getId()
        );

        if (!empty($tenant)) {
            return $tenant;
        }

        $tenant = new Tenant();
        $names  = ImportMapping::parseName($row[ImportMapping::KEY_TENANT_NAME]);
        $tenant->setFirstName($names[ImportMapping::FIRST_NAME_TENANT]);
        $tenant->setLastName($names[ImportMapping::LAST_NAME_TENANT]);
        $tenant->setEmail($row[ImportMapping::KEY_EMAIL]);
        $tenant->setEmailCanonical($row[ImportMapping::KEY_EMAIL]);
        $tenant->setPassword(md5(md5(1)));
        $tenant->setCulture($this->locale);

        return $tenant;
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

    /**
     * @param Import $import
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
        $import->setEmail($row[ImportMapping::KEY_EMAIL]);
        $import->setTenant($tenant);
        $import->setIsSkipped(false);
        if ($this->mapping->isSkipped($row)) {
            $import->setIsSkipped(true);
        }

        $import->setContract($contract = $this->getContract($import, $row));

        if ($contract && !$property = $contract->getProperty()) {
            $import->setAddress($row[ImportMapping::KEY_STREET].','.$row[ImportMapping::KEY_CITY]);
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

        if ($contractWaiting->getId()) {
            $import->setHasContractWaiting(true);
            $tenant->setFirstName($contractWaiting->getFirstName());
            $tenant->setLastName($contractWaiting->getLastName());
        }

        if (!$import->getIsSkipped() && $form = $this->getForm($import)) {
            $import->setForm($form);
        }

        if (!$this->isCreateCsrfToken && !$import->getIsSkipped()) {
            $errors = $this->runFormValidation($form, $lineNumber, $token);
            if ($this->isUsedResidentId($import->getResidentMapping())) {
                $errors[$lineNumber][$form->getName()][ImportMapping::KEY_RESIDENT_ID] = $this->translator->trans(
                    'error.residentId.already_use'
                );
            }
            $import->setErrors($errors);
        }

        return $import;
    }

    public function getContractWaiting(
        Tenant $tenant,
        Contract $contract,
        ResidentMapping $residentMapping
    ) {
        $contractWaiting = $this->mapping->createContractWaiting(
            $tenant,
            $contract,
            $residentMapping
        );

        if (!$contractWaiting->getProperty()) {
            return $contractWaiting;
        }
        /**
         * @var $contractWaitingInDb ContractWaiting
         */
        $contractWaitingInDb = $this->em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            $contractWaiting->getImportDataForFind()
        );

        if ($contractWaitingInDb) {
            //Do update some fields
            $contractWaitingInDb->setRent($contractWaiting->getRent());
            $contractWaitingInDb->setIntegratedBalance($contractWaiting->getIntegratedBalance());
            $contractWaitingInDb->setStartAt($contractWaiting->getStartAt());
            $contractWaitingInDb->setFinishAt($contractWaiting->getFinishAt());
            return $contractWaitingInDb;
        }

        return $contractWaiting;

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
        $data       = $this->mapping->getFileData($this->storage->getFileLine(), $rowCount = self::ROW_ON_PAGE);
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
            foreach ($mappedData as $import) {
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
     * Return array of errors and persisting entity, also fill emailSendingQueue if needed
     *
     * @param Import $import
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
                    /**
                     * @var $contract Contract
                     */
                    $contract = $form->getData();
                    if ($import->getHasContractWaiting()) {
                        $sendInvite = $form->get('sendInvite')->getNormData();
                        $this->processContractWaiting(
                            $import->getTenant(),
                            $contract,
                            $import->getResidentMapping(),
                            $sendInvite
                        );
                        break;
                    }

                    if ($this->storage->isMultipleProperty()) {
                        $isSingle = $form->get('isSingle')->getData();
                        $this->afterSubmitForm($contract, $isSingle);
                        $unitMapping = $form->get('unitMapping')->getData();
                        if (!$unitMapping->getUnit()) {
                            $unitMapping->setUnit($contract->getUnit());
                        }
                        $this->em->persist($unitMapping);
                    }
                    $this->persistContract($import, $contract);
                    if (!$contract->getId()) {
                        $this->emailSendingQueue[] = $contract;
                    } elseif (!is_null($import->getOperation())) {
                        //see logic in setOperation method
                        $operation = $form->get('operation')->getData();
                        $this->persistOperation($import->getTenant(), $operation, $contract);
                    }
                    $residentMapping = $form->get('residentMapping')->getData();
                    $this->em->persist($residentMapping);
                    break;
                case 'import_new_user_with_contract':
                    $data       = $form->getData();
                    $tenant     = $data['tenant'];
                    /**
                     * @var $contract Contract
                     */
                    $contract = $data['contract'];
                    if ($this->storage->isMultipleProperty()) {
                        $isSingle = $form->get('contract')->get('isSingle')->getData();
                        $this->afterSubmitForm($contract, $isSingle);
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
                        $this->em->persist($tenant);
                        $this->em->persist($residentMapping);
                        $this->persistContract($import, $contract);
                        if ($data['sendInvite']) {
                            $this->emailSendingQueue[] = $contract;
                        }
                    }
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
     * @param Tenant $tenant
     * @param Contract $contract
     * @param ResidentMapping $residentMapping
     * @param boolean $sendInvite
     */
    protected function processContractWaiting(
        Tenant $tenant,
        Contract $contract,
        ResidentMapping $residentMapping,
        $sendInvite
    ) {
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
     * Use only for multiple property
     *
     * @param Contract $contract
     * @param $isSingle
     */
    protected function afterSubmitForm(Contract $contract, $isSingle)
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
     * @param Import $import
     * @param Contract $contract
     */
    protected function persistContract(ModelImport $import, Contract $contract)
    {
        $today = new DateTime();
        if (($finishAt = $contract->getFinishAt()) && $finishAt <= $today) { //set status of contract to finished...
            $contract->setStatus(ContractStatus::FINISHED);

            if ($contract->getIntegratedBalance() > 0) {
                $contract->setUncollectedBalance($contract->getIntegratedBalance());
            }
        }

        $this->em->persist($contract);
    }

    /**
     * @param Import $import
     * @param Operation $operation
     */
    protected function persistOperation(Tenant $tenant, Operation $operation, Contract $contract)
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

        $this->em->persist($order);
        $this->em->persist($operation);
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
         * @var $contract Contract
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

    /**
     * @return Property|null
     */
    protected function getProperty($row)
    {
        if (!$this->storage->isMultipleProperty()) {
            return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
        }

        $property = $this->mapping->createProperty($row);
        if (empty($property) || !$property->getNumber() || !$property->getStreet()) {
            return null;
        }

        $params = array(
            'jb'        => $property->getJb(),
            'kb'        => $property->getKb(),
            'number'    => $property->getNumber(),
        );

        $propertyInDataBase = $this->em->getRepository('RjDataBundle:Property')->findOneBy($params);

        if ($propertyInDataBase) {
            return $propertyInDataBase;
        }

        $this->googleService->savePlace($property);

        return $property;
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
}
