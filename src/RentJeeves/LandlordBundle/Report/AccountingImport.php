<?php

namespace RentJeeves\LandlordBundle\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Validator\DateWithFormat;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportNewContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Model\Import;
use Symfony\Bridge\Twig\Node\TransDefaultDomainNode;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use CreditJeeves\CoreBundle\Translation\Translator;
use \DateTime;
use \Exception;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import")
 */
class AccountingImport
{
    use FormErrors;

    const IMPORT_FILE_PATH = 'importFileName';

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    const IMPORT_TEXT_DELIMITER = 'importTextDelimiter';

    const IMPORT_FIELD_DELIMITER = 'importFieldDelimiter';

    const IMPORT_FILE_LINE = 'importFileLine';

    const IMPORT_MAPPING = 'importMapping';

    const FIRST_NAME_TENANT = 'first_name';

    const LAST_NAME_TENANT = 'last_name';

    const KEY_UNIT = 'unit';

    const KEY_RESIDENT_ID = 'resident_id';

    const KEY_TENANT_NAME = 'tenant_name';

    const KEY_RENT = 'rent';

    const KEY_MOVE_IN = 'move_in';

    const KEY_MOVE_OUT = 'move_out';

    const KEY_LEASE_END = 'lease_end';

    const KEY_BALANCE = 'balance';

    const KEY_EMAIL = 'email';

    const ROW_ON_PAGE = 10;

    protected $skipValue = array(
        self::KEY_RESIDENT_ID => 'VACANT',
    );

    protected $session;

    protected $reader;

    protected $mapping = array();

    protected $em;

    /**
     * @var Landlord
     */
    protected $user;

    protected $formFactory;

    protected $formCsrfProvider;

    protected $validateData = false;

    protected $mailer;

    /**
     * @InjectParams({
     *     "session"          = @Inject("session"),
     *     "reader"           = @Inject("reader.csv"),
     *     "em"               = @Inject("doctrine.orm.default_entity_manager"),
     *     "translator"       = @Inject("translator"),
     *     "context"          = @Inject("security.context"),
     *     "formFactory"      = @Inject("form.factory"),
     *     "translator"       = @Inject("translator"),
     *     "formCsrfProvider" = @Inject("form.csrf_provider"),
     *     "mailer"           = @Inject("project.mailer")
     * })
     */
    public function __construct(
        Session $session,
        CsvFileReader $reader,
        EntityManager $em,
        Translator $translator,
        SecurityContext $context,
        $formFactory,
        $translator,
        CsrfTokenManagerAdapter $formCsrfProvider,
        Mailer $mailer
    )
    {
        $this->session = $session;
        $this->reader  = $reader;
        $data          = $this->getImportData();
        $this->reader->setDelimiter($data[self::IMPORT_FIELD_DELIMITER]);
        $this->reader->setEnclosure($data[self::IMPORT_TEXT_DELIMITER]);
        $this->em          = $em;
        $this->user        = $context->getToken()->getUser();
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->formCsrfProvider = $formCsrfProvider;
        $this->mailer = $mailer;
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

    public function getValidatorsByKey($key)
    {
        return $this->validatorsField[$key];
    }

    public function setFilePath($fileName)
    {
        $this->session->set(self::IMPORT_FILE_PATH, $fileName);
    }

    public function getFileLine()
    {
        return $this->session->get(self::IMPORT_FILE_LINE, 0);
    }

    public function setFileLine($fileLine)
    {
        return $this->session->set(self::IMPORT_FILE_LINE, $fileLine);
    }

    public function setPropertyId($propertyId)
    {
        $this->session->set(self::IMPORT_PROPERTY_ID, $propertyId);
    }

    public function getPropertyId()
    {
        return $this->session->get(self::IMPORT_PROPERTY_ID);
    }

    public function setTextDelimiter($value)
    {
        $this->session->set(self::IMPORT_TEXT_DELIMITER, $value);
    }

    public function setFieldDelimiter($value)
    {
        $this->session->set(self::IMPORT_FIELD_DELIMITER, $value);
    }

    public function setMapping(array $value)
    {
        $this->session->set(self::IMPORT_MAPPING, json_encode($value));
    }

    public function getMapping()
    {
        if (empty($this->mapping)) {
            $this->mapping = json_decode($this->session->get(self::IMPORT_MAPPING), true);
        }

        return $this->mapping;
    }

    /**
     * Array if data valid and false if not valid
     *
     * @return array|bool
     */
    public function getImportData()
    {
        $data = array(
            self::IMPORT_FILE_PATH       => $this->session->get(self::IMPORT_FILE_PATH),
            self::IMPORT_PROPERTY_ID     => $this->session->get(self::IMPORT_PROPERTY_ID),
            self::IMPORT_TEXT_DELIMITER  => $this->session->get(self::IMPORT_TEXT_DELIMITER),
            self::IMPORT_FIELD_DELIMITER => $this->session->get(self::IMPORT_FIELD_DELIMITER),
        );

        foreach ($data as $value) {
            if (empty($value)) {
                return false;
            }
        }

        $data[self::IMPORT_MAPPING] = $this->session->get(self::IMPORT_MAPPING);
        if (!empty($data[self::IMPORT_MAPPING])) {
            $data[self::IMPORT_MAPPING] = json_decode($data[self::IMPORT_MAPPING], true);
        }
        $data[self::IMPORT_FILE_PATH] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data[self::IMPORT_FILE_PATH];

        return $data;
    }

    /**
     * Array if valid and string for invalid
     *
     * @return array|string
     */
    public function getDataForMapping()
    {
        $data = $this->getImportData();
        $data = $this->reader->read($data[self::IMPORT_FILE_PATH], 0, 3);

        if (count($data) < 1) {
            return 'csv.file.too.small1';
        }

        if (count($data[1]) < 8) {
            return 'csv.file.too.small2';
        }

        return $data;
    }

    /**
     * $mappedData will be have next keys of array
     *
     *
     * Detailts about each key https://credit.atlassian.net/wiki/display/RT/Accounting+File+Import
     *
     * @return array
     */
    protected function mappingRow(array $row)
    {
        $mapping    = $this->getMapping();
        $mappedData = array();
        $i          = 1;
        foreach ($row as $value) {
            if (isset($mapping[$i])) {
                $mappedData[$mapping[$i]] = $value;
            }
            $i++;
        }

        return $mappedData;
    }

    protected function getFileData($mapped = true, $offset = null, $rowCount = null)
    {
        $mappedData = array();
        $data       = $this->getImportData();
        $data       = $this->reader->read($data[self::IMPORT_FILE_PATH], $offset, $rowCount);

        if (!$mapped) {
            return $data;
        }

        foreach ($data as $key => $values) {
            $row          = $this->mappingRow($values);
            $mappedData[] = $row;
        }

        return $mappedData;
    }

    public function isSkipped($row)
    {
        $skip = false;

        foreach ($this->skipValue as $keySkip => $valueSkip) {
            if ($row[$keySkip] === $valueSkip) {
                $skip = true;
                break;
            }
        }

        return $skip;
    }

    protected function getProperty()
    {
        return $this->em->getRepository('RjDataBundle:Property')->find($this->getPropertyId());
    }

    protected function getUnit($row)
    {
        $params = array(
            'property' => $this->getPropertyId(),
            'name'     => $row[self::KEY_UNIT],
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
        $unit->setName($row[self::KEY_UNIT]);
        $unit->setProperty($this->getProperty());
        $unit->setHolding($this->user->getHolding());
        $unit->setGroup($this->user->getCurrentGroup());
        return $unit;
    }

    public function getDateByField($field)
    {
        try {
            $date = DateTime::createFromFormat('m/d/Y', $field);
        } catch(Exception $e) {
            return null;
        }

        return ($date)? $date : null;
    }

    //@TODO try to make in this place serializer if that will be possible
    protected function getImport($row, $lineNumber)
    {
        $import = new Import();
        $tenant = $this->em->getRepository('RjDataBundle:Tenant')->getTenantForImport(
            $email = $row[self::KEY_EMAIL],
            $propertyId = $this->getPropertyId(),
            $unitName = $row[self::KEY_UNIT]
        );

        if (empty($tenant)) {
            $tenant = new Tenant();
            $names  = $this::parseName($row[self::KEY_TENANT_NAME]);
            $tenant->setFirstName($names[self::FIRST_NAME_TENANT]);
            $tenant->setLastName($names[self::LAST_NAME_TENANT]);
            $tenant->setEmail($row[self::KEY_EMAIL]);
            $tenant->setEmailCanonical($row[self::KEY_EMAIL]);
            $tenant->setPassword(md5(md5(1)));
        } else {
            //For the same tenant and different contract/unit we have the same result
            //That's why we need detach
            $this->em->detach($tenant);
        }

        $import->setTenant($tenant);
        $import->setIsSkipped(false);
        if ($this->isSkipped($row)) {
            $import->setIsSkipped(true);
        }

        if (!$tenant->getResidentId()) {
            $tenant->setResidentId($row[self::KEY_RESIDENT_ID]);
        }

        $contracts = $tenant->getContracts();
        $contract  = $contracts->first();

        if (!$contract) {
            $contract = new Contract();
            $contract->setStatus(ContractStatus::INVITE);
            $contract->setProperty($this->getProperty());
            $contract->setGroup($this->user->getCurrentGroup());
            $contract->setHolding($this->user->getHolding());
            $tenant->addContract($contract);
            $contract->setTenant($tenant);
            $contract->setUnit($this->getUnit($row));
            $contract->setCreatedAt(new DateTime());
        }

        $contract->setUpdatedAt(new DateTime());
        $contract->setStartAt($this->getDateByField($row[self::KEY_MOVE_IN]));
        $contract->setFinishAt($this->getDateByField($row[self::KEY_LEASE_END]));
        if ($row[self::KEY_MOVE_OUT] && $contract->getStatus() !== ContractStatus::FINISHED) {
            $import->setMoveOut($this->getDateByField($row[self::KEY_MOVE_OUT]));
            $contract->setStatus(ContractStatus::FINISHED);
        } elseif ($row[self::KEY_MOVE_OUT] && $contract->getStatus() === ContractStatus::FINISHED) {
            $import->setIsSkipped(true);
        }

        $contract->setImportedBalance($row[self::KEY_BALANCE]);
        $contract->setRent($row[self::KEY_RENT]);
        $tenantId   = $tenant->getId();
        $contractId = $contract->getId();
        $token   = (!$this->validateData) ? $this->formCsrfProvider->generateCsrfToken($lineNumber) : '';
        //Update contract or Create contract
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
        }

        //Create contract and create user
        if (empty($tenantId) && in_array(
                $contract->getStatus(),
                array(
                    ContractStatus::INVITE
                )) && empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm();
            $form->get('tenant')->setData($tenant);
            $form->get('contract')->setData($contract);
        }

        if (isset($form)) {
            $form->get('_token')->setData($token);
            $import->setForm($form);
        }

        $import->setCsrfToken($token);
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

    public function countData()
    {
        return count($this->getFileData());
    }

    public function getMappedData()
    {
        $data = $this->getFileData(true, $this->getFileLine(), $rowCount = self::ROW_ON_PAGE);
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
        $mappedData = $this->getMappedData();
        $errors = array();
        $this->validateData = true;
        foreach ($data as $formData) {
            $formData['line'] = (int) $formData['line'];
            /**
             * @var $import Import
             */
            foreach ($mappedData as $import) {
                if ($import->getNumber() === $formData['line']) {
                    $this->bindForm($import, $formData, $errors);
                }
            }
        }
        $this->em->flush();
        $this->validateData = false;
        return $errors;
    }

    protected function bindForm(Import $import, $postData, &$errors)
    {
        self::prepeaSubmit($postData);
        if ($import->getIsSkipped() || isset($postData['contract']['skip']) || isset($postData['skip'])) {
            return;
        }
        $line = $postData['line'];
        unset($postData['line']);
        $contract = $import->getTenant()->getContracts()->first();
        $contractId = $contract->getId();
        $form = $import->getForm();
        if (isset($postData['_token'])) {
            $form->submit($postData);
            $isCsrfTokenValid = $this->formCsrfProvider->isCsrfTokenValid($line, $postData['_token']);
            if ($form->isValid() && $isCsrfTokenValid) {
                //Do save
                switch ($form->getName()) {
                    case 'import_contract':
                        /**
                         * Read line 376, detach tenant
                         *
                         * @var $contract Contract
                         */
                        $contract = $form->getData();
                        $unit = $contract->getUnit();
                        $this->em->merge($contract->getTenant());
                        $this->em->merge($contract);
                        $this->em->persist($unit);
                        if (!empty($contractId)) {
                            $this->em->persist($contract);
                        }
                        break;
                    case 'import_new_user_with_contract':
                        $data = $form->getData();
                        $tenant = $data['tenant'];
                        $contract = $data['contract'];
                        $unit = $contract->getUnit();
                        $sendInvite = $data['sendInvite'];
                        $this->em->persist($tenant);
                        $this->em->persist($unit);
                        $this->em->persist($contract);
                        if ($sendInvite) {
                            $this->mailer->sendRjTenantInvite($tenant, $this->user, $contract);
                        }
                        break;
                }

            } elseif (!$isCsrfTokenValid) {
                //@TODO move to trans
                $errors[$line] = array(
                    '_global' => 'The CSRF token is invalid. Please try to resubmit the form',
                );
            } else {
                $errors[$line] = $this->getFormErrors($form);
            }
        } elseif ($import->getMoveOut() &&
            $contract->getStatus() === ContractStatus::FINISHED &&
            !empty($contractId)
        ) {
            $this->em->persist($contract);
        }
    }

    public static function prepeaSubmit(&$formData)
    {
        $replaces = array(
            'import_new_user_with_contract' => 30,
            'import_contract'               => 16,
        );

        foreach ($formData as $key => $value) {
            foreach ($replaces as $replace => $number) {
                if (preg_match('/'.$replace.'\[/', $key)) {
                    $newKey = substr($key, $number, strlen($key)-1);
                    $formData[$newKey] = $value;
                    unset($formData[$key]);
                }
            }
        }
    }

    public static function parseName($name)
    {
        $names = explode(' ', $name);

        switch (count($names)) {
            case 1:
                $data = array(
                    self::FIRST_NAME_TENANT => $names[0],
                    self::LAST_NAME_TENANT  => $names[0],
                );
                break;
            case 2:
                $data = array(
                    self::FIRST_NAME_TENANT => $names[0],
                    self::LAST_NAME_TENANT  => $names[1],
                );
                break;
            case 3:
                $data = array(
                    self::FIRST_NAME_TENANT => implode(' ', array($names[0], $names[1])),
                    self::LAST_NAME_TENANT  => $names[2],
                );
                break;
            case 4:
                $data = array(
                    self::FIRST_NAME_TENANT => implode(' ', array($names[0], $names[1])),
                    self::LAST_NAME_TENANT  => implode(' ', array($names[1], $names[2])),
                );
                break;
            default:
                $data = array(
                    self::FIRST_NAME_TENANT => '',
                    self::LAST_NAME_TENANT  => '',
                );
        }

        return $data;
    }
}
