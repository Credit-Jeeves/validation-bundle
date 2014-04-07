<?php

namespace RentJeeves\LandlordBundle\Accounting;

use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
     * Values which we skip
     *
     * @var array
     */
    protected $skipValues = array(
        ImportMapping::KEY_RESIDENT_ID => 'VACANT',
    );

    /**
     * @var bool
     */
    protected $isCreateCsrfToken = false;

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
     *     "mapping"          = @Inject("accounting.import.mapping")
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
        ImportMapping $mapping
    )
    {
        $this->em               = $em;
        $this->user             = $context->getToken()->getUser();
        $this->formFactory      = $formFactory;
        $this->translator       = $translator;
        $this->formCsrfProvider = $formCsrfProvider;
        $this->mailer           = $mailer;
        $this->mapping          = $mapping;
        $this->storage          = $storage;
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

    public function isSkipped($row)
    {
        $skip = false;

        foreach ($this->skipValues as $keySkip => $valueSkip) {
            if ($row[$keySkip] === $valueSkip) {
                $skip = true;
                break;
            }
        }

        return $skip;
    }

    public function getDateByField($field)
    {
        try {
            $date = DateTime::createFromFormat('m/d/Y', $field);
        } catch (Exception $e) {
            return null;
        }

        return ($date) ? $date : null;
    }

    protected function getUnit($row)
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

    protected function createContract($row, $tenant)
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
        $tenant->addContract($contract);
        $contract->setTenant($tenant);
        $contract->setUnit($this->getUnit($row));

        return $contract;
    }

    protected function getContract($row, $tenant)
    {
        if (!$tenant->getId()) {
            $contract = $this->createContract($row, $tenant);
        } else {
            $contract = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                $row[ImportMapping::KEY_UNIT]
            );
            $contract = (!empty($contract)) ? $contract : $this->createContract($row, $tenant);
        }

        //set data from csv file
        $contract->setImportedBalance($row[ImportMapping::KEY_BALANCE]);
        $contract->setRent($row[ImportMapping::KEY_RENT]);
        $contract->setStartAt($this->getDateByField($row[ImportMapping::KEY_MOVE_IN]));
        $contract->setFinishAt($this->getDateByField($row[ImportMapping::KEY_LEASE_END]));

        return $contract;
    }

    /**
     * @param array $row
     *
     * @return Tenant
     */
    protected function getTenant($row)
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

        return $tenant;
    }

    protected function validateFieldWhichNotCheckByForm(ModelImport $import)
    {
        $tenant   = $import->getTenant();
        $contract = $import->getContract();

        if (preg_match('/^[A-Za-z_0-9\-]{1,50}$/i', $contract->getUnit()->getName())) {
            $import->setIsValidUnit(true);
        }

        if (preg_match('/^[A-Za-z_0-9]{1,100}$/i', $tenant->getResidentId())) {
            $import->setIsValidResidentId(true);
        }
    }

    protected function setForm(ModelImport $import)
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
            $form = $this->getContractForm();
            $form->setData($contract);

            $import->setForm($form);
        }


        //Create contract and create user
        if (empty($tenantId) &&
            $contract->getStatus() === ContractStatus::INVITE &&
            empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm();
            $form->get('tenant')->setData($tenant);
            $form->get('contract')->setData($contract);

            $import->setForm($form);
        }

        //Finish exist contract form
        if ($contract->getStatus() === ContractStatus::FINISHED && !$import->getIsSkipped()) {
            $form = $this->getContractFinishForm();
            $form->setData($contract);

            $import->setForm($form);
        }
    }

    protected function setOperation(ModelImport $import, $row)
    {
        if (!$this->mapping->isHavePaymentMapping($row)) {
            return;
        }

        $contract = $import->getContract();
        if ($contract->getStatus() !== ContractStatus::CURRENT) {
            return;
        }

        $tenant = $import->getTenant();
        $amount = $row[ImportMapping::KEY_PAYMENT_AMOUNT];
        $paidFor = $this->getDateByField($row[ImportMapping::KEY_PAYMENT_DATE]);

        if (!$paidFor) {
            return;
        }

        $operation = $this->em->getRepository('DataBundle:Operation')->getOperationImport(
             $tenant,
             $contract,
             $paidFor,
             $amount
        );

        //We can't create double payment
        if ($operation) {
            return;
        }

        $operation = new Operation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($amount);

        $import->setOperation($operation);
    }

    /**
     * @param array $row
     * @param integer $lineNumber
     *
     * @return ModelImport
     */
    protected function getImport($row, $lineNumber)
    {
        $import = new ModelImport();
        $tenant = $this->getTenant($row);
        $import->setIsSkipped(false);
        if ($this->isSkipped($row)) {
            $import->setIsSkipped(true);
        }

        if (!$tenant->getResidentId() && !empty($row[ImportMapping::KEY_RESIDENT_ID])) {
            $tenant->setResidentId($row[ImportMapping::KEY_RESIDENT_ID]);
        }

        $contract = $this->getContract($row, $tenant);

        if (!empty($row[ImportMapping::KEY_MOVE_OUT])) {
            $import->setMoveOut($this->getDateByField($row[ImportMapping::KEY_MOVE_OUT]));
            $contract->setStatus(ContractStatus::FINISHED);
        }

        $import->setTenant($tenant);
        $import->setContract($contract);

        $token      = (!$this->isCreateCsrfToken) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        $import->setCsrfToken($token);
        $this->validateFieldWhichNotCheckByForm($import);

        if ($import->isValid()) {
            $this->setForm($import);
        }

        $this->setOperation($import, $row);

        return $import;
    }

    /**
     * @return Form
     */
    public function getContractForm()
    {
        return $this->createForm(new ImportContractType());
    }

    /**
     * @return Form
     */
    public function getCreateUserAndCreateContractForm()
    {
        return $this->createForm(new ImportNewUserWithContractType());
    }

    /**
     * @return Form
     */
    public function getContractFinishForm()
    {
        return $this->createForm(new ImportContractFinishType());
    }

    public function getMappedData()
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

    public function saveForms($data)
    {
        $mappedData         = $this->getMappedData();
        $errors             = array();
        $this->isCreateCsrfToken = true;
        $lines = array();
        foreach ($data as $formData) {
            $formData['line'] = (int)$formData['line'];

            /**
             * @var $import Import
             */
            foreach ($mappedData as $import) {
                if ($import->getNumber() === $formData['line']) {
                    $lines[] = $formData['line'];
                    $this->bindForm($import, $formData, $errors);
                }
            }
        }
        $this->isCreateCsrfToken = false;

        if (!empty($errors)) {
            return $errors;
        }

        $this->em->flush();
        $this->clearTokens($lines);
        $this->sendInviteEmail();

        return array();
    }

    protected function bindForm(ModelImport $import, $postData, &$errors)
    {
        $form = $import->getForm();
        if (!$form) {
            return;
        }

        self::prepareSubmit($postData, $form->getName());
        if ($import->getIsSkipped() ||
            isset($postData['contract']['skip']) ||
            isset($postData['skip']) ||
            !$import->isValid()
        ) {
            return;
        }

        $line = $postData['line'];
        unset($postData['line']);
        $form = $import->getForm();
        if (!isset($postData['_token'])) {
            return;
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
                    if (!$contract->getId()) {
                        $this->emailSendingQueue[] = $import;
                    }
                    $this->em->persist($contract);
                    $this->em->persist($contract->getUnit());
                    break;
                case 'import_new_user_with_contract':
                    $data       = $form->getData();
                    $tenant     = $data['tenant'];
                    $contract   = $data['contract'];
                    $unit       = $contract->getUnit();
                    $sendInvite = $data['sendInvite'];
                    $this->em->persist($tenant);
                    $this->em->persist($unit);
                    $this->em->persist($contract);
                    if ($sendInvite) {
                        $this->emailSendingQueue[] = $import;
                    }
                    break;
            }

            return;
        }

        $errors[$line] = $this->getFormErrors($form);
        if (!$isCsrfTokenValid) {
            $errors[$line]['_global'] = $this->translator->trans('csrf.token.is.invalid');
        }
    }

    protected function clearTokens(array $lines)
    {
        $tokenManager = $this->formCsrfProvider->getTokenManager();
        foreach ($lines as $line) {
            $tokenManager->removeToken($line);
        }
    }

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


    protected function getProperty()
    {
        return $this->em->getRepository('RjDataBundle:Property')->find($this->storage->getPropertyId());
    }

    /**
     * We need remove form name from key of array and leave just name form field
     * it's need for form submit
     */
    public static function prepareSubmit(&$formData, $formName)
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
