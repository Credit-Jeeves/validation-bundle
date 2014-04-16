<?php

namespace RentJeeves\LandlordBundle\Accounting;

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
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Exception\ImportProcessException;
use RentJeeves\LandlordBundle\Form\ImportContractFinishType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\SecurityContext;
use CreditJeeves\CoreBundle\Translation\Translator;
use \DateTime;
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
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Landlord
     */
    protected $user;

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
     * @InjectParams({
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "translator"       = @Inject("translator"),
     *     "context"          = @Inject("security.context"),
     *     "formFactory"      = @Inject("form.factory"),
     *     "translator"       = @Inject("translator"),
     *     "formCsrfProvider" = @Inject("form.csrf_provider"),
     *     "mailer"           = @Inject("project.mailer"),
     *     "storage"          = @Inject("accounting.import.storage"),
     *     "mapping"          = @Inject("accounting.import.mapping"),
     *     "validator"        = @Inject("validator"),
     *     "locale"           = @Inject("%kernel.default_locale%")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        SecurityContext $context,
        FormFactory $formFactory,
        Translator $translator,
        CsrfTokenManagerAdapter $formCsrfProvider,
        Mailer $mailer,
        ImportStorage $storage,
        ImportMapping $mapping,
        Validator $validator,
        $locale
    ) {
        $this->em               = $em;
        $this->user             = $context->getToken()->getUser();
        $this->formFactory      = $formFactory;
        $this->translator       = $translator;
        $this->formCsrfProvider = $formCsrfProvider;
        $this->mailer           = $mailer;
        $this->mapping          = $mapping;
        $this->storage          = $storage;
        $this->validator        = $validator;
        $this->locale           = $locale;
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
            $date = DateTime::createFromFormat('m/d/Y', $field);
        } catch (Exception $e) {
            return null;
        }

        return ($date) ? $date : null;
    }

    /**
     * @return Form
     */
    public function getContractForm(Tenant $tenant, $isUseToken = true, $isUseOperation = true)
    {
        return $this->createForm(
            new ImportContractType($tenant, $this->em, $this->translator, $isUseToken, $isUseOperation)
        );
    }

    /**
     * @return Form
     */
    public function getCreateUserAndCreateContractForm()
    {
        return $this->createForm(new ImportNewUserWithContractType(new Tenant(), $this->em, $this->translator));
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

        $contract->setProperty($this->getProperty());
        $contract->setGroup($this->user->getCurrentGroup());
        $contract->setHolding($this->user->getHolding());
        $contract->setTenant($tenant);
        $contract->setUnit($this->getUnit($row));

        $tenant->addContract($contract);

        return $contract;
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

    /**
     * @param $row
     *
     * @return Unit
     */
    protected function getUnit(array $row)
    {
        $params = array(
            'property' => $this->storage->getPropertyId(),
            'name'     => $row[ImportMapping::KEY_UNIT],
        );

        if ($group = $this->user->getCurrentGroup()) {
            $params['group'] = $group;
        }

        if ($holding = $this->user->getHolding()) {
            $params['holding'] = $holding;
        }

        $unit = $this->em->getRepository('RjDataBundle:Unit')->findOneBy($params);
        if ($unit) {
            return $unit;
        }
        $unit = new Unit();
        $unit->setName($row[ImportMapping::KEY_UNIT]);
        $unit->setProperty($this->getProperty());
        $unit->setHolding($this->user->getHolding());
        $unit->setGroup($this->user->getCurrentGroup());
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
        $tenant  = $import->getTenant();

        if (!$tenant->getId()) {
            $contract = $this->createContract($row, $tenant);
        } else {
            //@TODO make one query, not two as now
            $contractCurrentApprove = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                $row[ImportMapping::KEY_UNIT]
            );
            //Check for don't send many invite for the same contract
            $contractInvite = $this->em->getRepository('RjDataBundle:Contract')->getContractInviteForImport(
                $tenant->getId(),
                $row[ImportMapping::KEY_UNIT]
            );

            /**
             * Checking exist contract in DB
             */
            if (empty($contractInvite) && !empty($contractCurrentApprove)) {
                $contract = $contractCurrentApprove;
            } elseif (!empty($contractInvite) && empty($contractCurrentApprove)) {
                $contract = $contractInvite;
            } else {
                $contract = $this->createContract($row, $tenant);
            }
        }

        //set data from csv file
        $contract->setImportedBalance($row[ImportMapping::KEY_BALANCE]);
        $contract->setRent($row[ImportMapping::KEY_RENT]);
        $contract->setStartAt($this->getDateByField($row[ImportMapping::KEY_MOVE_IN]));
        $contract->setFinishAt($this->getDateByField($row[ImportMapping::KEY_LEASE_END]));

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
        if (!$this->mapping->isHavePaymentMapping($row)) {
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
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => $row[ImportMapping::KEY_EMAIL]
            )
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

        $tenantId   = $tenant->getId();
        $contractId = $contract->getId();

        //Update contract or Create contract with exist User
        if (($tenantId &&
                in_array(
                    $contract->getStatus(),
                    array(
                        ContractStatus::APPROVED,
                        ContractStatus::CURRENT
                    )
                )
                && $contractId)
            || ($tenantId && empty($contractId))
        ) {
            $isUseOperation = ($import->getOperation() === null)? false : true;
            $form = $this->getContractForm($tenant, $isUseToken = true, $isUseOperation);
            $form->setData($contract);
            $form->get('residentMapping')->setData($import->getResidentMapping());
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
            $form->get('contract')->get('residentMapping')->setData($import->getResidentMapping());
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
    protected function isValidNotEditedFields(ModelImport $import)
    {
        $unit = $import->getContract()->getUnit();
        $errors = $this->validator->validate($unit, array("import"));
        if (count($errors) > 0) {
            return false;
        }

        $residentMapping = $import->getResidentMapping();
        $errors = $this->validator->validate($residentMapping, array("import"));
        if (count($errors) > 0) {
            return false;
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
        $import->setTenant($tenant);
        $import->setIsSkipped(false);
        if ($this->mapping->isSkipped($row)) {
            $import->setIsSkipped(true);
        }

        $contract = $this->getContract($import, $row);
        if ($contract) {
            $import->setContract($contract);
        }

        $token      = (!$this->isCreateCsrfToken) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        $import->setCsrfToken($token);

        if ($operation = $this->getOperation($import, $row)) {
            $import->setOperation($operation);
        }

        $import->setResidentMapping($this->getResident($tenant, $row));

        if (!$import->getIsSkipped() && $form = $this->getForm($import)) {
            $import->setForm($form);
        }

        if (!$this->isCreateCsrfToken && !$import->getIsSkipped()) {
            $import->setErrors($this->runFormValidation($form, $lineNumber, $token));
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
        $data       = $this->mapping->getFileData($this->storage->getFileLine(), $rowCount = self::ROW_ON_PAGE);
        $collection = new ArrayCollection(array());
        foreach ($data as $key => $values) {
            $import = $this->getImport($values, $key);
            $import->setNumber($key);
            $collection->add($import);
        }
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

                    if (!$resultBind &&
                        !is_null($form = $import->getForm()) &&
                        !$this->isValidNotEditedFields($import)
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

        if (!$this->isValidNotEditedFields($import)) {
            return false;
        }

        self::prepareSubmit($postData, $form->getName(), $import);

        if ($import->getIsSkipped() ||
            isset($postData['contract']['skip']) ||
            isset($postData['skip'])
        ) {
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
                    $contract = $form->getData();
                    $this->em->persist($contract->getUnit());
                    $this->persistContract($import, $contract);
                    if (!$contract->getId()) {
                        $this->emailSendingQueue[] = $import;
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
                    $residentMapping = $form->get('contract')->get('residentMapping')->getData();
                    $tenant     = $data['tenant'];
                    $contract   = $data['contract'];
                    $unit       = $contract->getUnit();
                    $sendInvite = $data['sendInvite'];
                    $this->em->persist($tenant);
                    $this->em->persist($residentMapping);
                    $this->em->persist($unit);
                    $this->persistContract($import, $contract);
                    if ($sendInvite) {
                        $this->emailSendingQueue[] = $import;
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
     * @param Import $import
     * @param Contract $contract
     */
    protected function persistContract(ModelImport $import, Contract $contract)
    {
        $today = new DateTime();
        if ($contract->getFinishAt() <= $today) { //set status of contract to finished...
            $contract->setStatus(ContractStatus::FINISHED);
        }

        if ($contract->getImportedBalance() > 0) {
            $contract->setUncollectedBalance($contract->getImportedBalance());
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
         * @var $import Import
         */
        foreach ($this->emailSendingQueue as $import) {
            $this->mailer->sendRjTenantInvite($import->getTenant(), $this->user, $import->getContract());
        }

        $this->emailSendingQueue = array();
    }

    /**
     * @return Property|null
     */
    protected function getProperty()
    {
        return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
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

        //@TODO find better way
        // We need setup data which we use only for view on the client side.
        // See new form type,just for view cj2/src/RentJeeves/LandlordBundle/Form/Type/ViewType.php
        switch ($formName) {
            case 'import_new_user_with_contract':
                $formData['contract']['unit'] = array(
                    'name' => $import->getContract()->getUnit()->getName()
                );
                $formData['contract']['residentMapping'] = array(
                    'residentId' => $import->getResidentMapping()->getResidentId()
                );
                break;
            case 'import_contract':
                $formData['unit'] = array(
                    'name' => $import->getContract()->getUnit()->getName()
                );
                $formData['residentMapping'] = array(
                    'residentId' => $import->getResidentMapping()->getResidentId()
                );
                break;
            case 'import_contract_finish':
                break;
            default:
                throw new ImportProcessException("We have new form({$formName}) which must be added to this switch.");

        }
    }
}
