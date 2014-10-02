<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;
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
    use ImportForms;
    use ImportContract;
    use ImportTenant;
    use ImportResident;
    use ImportProperty;
    use ImportOperation;
    use ImportFormBind;
    use ImportUnit;

    const ROW_ON_PAGE = 9;

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
    protected $propertyProcess;

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
     *     "propertyProcess"  = @Inject("property.process"),
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
        PropertyProcess $propertyProcess,
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
        $this->propertyProcess  = $propertyProcess;
        $this->contractProcess  = $contractProcess;
    }

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

        if ($contractWaiting->getId() && !$import->getContract()->getId()) {
            $import->setHasContractWaiting(true);
            $tenant->setFirstName($contractWaiting->getFirstName());
            $tenant->setLastName($contractWaiting->getLastName());
        } elseif ($contract->getId() && $contractWaiting->getId()) {
            $this->em->remove($contractWaiting);
            $import->setHasContractWaiting(false);
            $this->em->flush();
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
}
