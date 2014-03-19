<?php

namespace RentJeeves\LandlordBundle\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use CreditJeeves\CoreBundle\Translation\Translator;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import")
 */
class AccountingImport
{
    const IMPORT_FILE_PATH = 'importFileName';

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    const IMPORT_TEXT_DELIMITER = 'importTextDelimiter';

    const IMPORT_FIELD_DELIMITER = 'importFieldDelimiter';

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

    const IS_VALID = 'isValid';

    const ERRORS = 'errors';

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

    /**
     * @InjectParams({
     *     "session"      = @Inject("session"),
     *     "reader"       = @Inject("reader.csv"),
     *     "em"           = @Inject("doctrine.orm.default_entity_manager"),
     *     "translator"   = @Inject("translator"),
     *     "context"      = @Inject("security.context"),
     *     "formFactory"  = @Inject("form.factory")
     * })
     */
    public function __construct(
        Session $session,
        CsvFileReader $reader,
        EntityManager $em,
        Translator $translator,
        SecurityContext $context,
        $formFactory
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

        if (count($data) < 2) {
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

        $mappedData[self::IS_VALID] = true;
        $mappedData[self::ERRORS]   = array();

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


    //@TODO need check exist unit in DB add group_id and holding_id
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

    //@TODO try to make in this place serializer
    // how we mark skip tenant? - solved
    // problem with query for getting tenant
    // problem with move_out field - solved
    //
    protected function getTenant($row)
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
            $contract->setUnit($this->getUnit($row));
        }

        $contract->setStartAt($row[self::KEY_MOVE_IN]);
        $contract->setFinishAt($row[self::KEY_LEASE_END]);
        if ($row[self::KEY_MOVE_OUT]) {
            $contract->setFinishAt($row[self::KEY_MOVE_OUT]);
            $contract->setStatus(ContractStatus::FINISHED);
        }
        $contract->setImportedBalance($row[self::KEY_BALANCE]);

        $tenantId   = $tenant->getId();
        $contractId = $contract->getId();

        //Update contract
        if ($tenantId &&
            in_array(
                $contract->getStatus(),
                array(
                    ContractStatus::APPROVED,
                    ContractStatus::CURRENT
                )
            )
            && $contractId
        ) {
            $form = $this->getUpdateContractForm();
        }

        //Create contract and create user
        if (empty($tenantId) && in_array(
                $contract->getStatus(),
                array(
                    ContractStatus::INVITE
                )) && empty($contractId)
        ) {
            $form = $this->getCreateUserAndCreateContractForm();
        }

        //Create contract
        if ($tenantId && empty($contractId)) {
            $form = $this->getCreateContractForm();
        }


        if (isset($form)) {
            $form = $form->createView();
            $token = $form['_token'];
            $import->setCsrfToken($token->vars["value"]);
        }

        return $import;
    }

    /**
     * @return Form
     */
    public function getUpdateContractForm()
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
    public function getCreateContractForm()
    {
        return $this->createForm(new ImportNewContractType());
    }

    public function getPages($limit)
    {
        $result = array();
        $total = $this->countData();
        $pages = ceil($total / $limit);
        if ($pages < 2) {
            return $result;
        }
        for ($i = 0; $i < $pages; $i++) {
            $result[] = $i + 1;
        }
        return $result;
    }

    public function countData()
    {
        return count($this->getFileData());
    }

    public function getMappedData($page, $rowCount)
    {
        $offset = $page * $rowCount - $rowCount;
        $data = $this->getFileData(true, $offset, $rowCount);
        $collection = new ArrayCollection(array());
        foreach ($data as $key => $values) {
            $collection->add($this->getTenant($values));
        }
        return $collection;
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
