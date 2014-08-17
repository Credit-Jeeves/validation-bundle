<?php

namespace RentJeeves\LandlordBundle\Accounting;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
use Symfony\Component\Form\Form;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.mapping")
 */
class ImportMapping
{
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

    const KEY_PAYMENT_AMOUNT = 'payment_amount';

    const KEY_PAYMENT_DATE = 'payment_date';

    const KEY_STREET = 'street';

    const KEY_CITY = 'city';

    const KEY_STATE = 'state';

    const KEY_ZIP = 'zip';

    const KEY_MONTH_TO_MONTH = 'month_to_month';

    const KEY_UNIT_ID = 'unit_id';

    protected $requiredKeysDefault = array(
        self::KEY_EMAIL,
        self::KEY_RESIDENT_ID,
        self::KEY_BALANCE,
        self::KEY_LEASE_END,
        self::KEY_MOVE_IN,
        self::KEY_MOVE_OUT,
        self::KEY_RENT,
        self::KEY_TENANT_NAME,
        self::KEY_UNIT,
    );

    protected $requiredKeysMultipleProperty = array(
        self::KEY_CITY,
        self::KEY_STREET,
        self::KEY_STATE,
        self::KEY_ZIP,
        self::KEY_UNIT_ID,
    );

    public static $mappingDates = array(
        'm/d/Y' => 'mm/dd/yyyy  (09/24/1998)',
        'Y-m-d' => 'yyyy-mm-dd (1998-09-24)',
        'Ymd' => 'yyyymmdd  (19980924)',
        'dmY' => 'ddmmyyyy  (24091998)',
        'mdY' => 'mmddyyyy  (09241998)',
        'Y/m/d' => 'yyyy/mm/dd (1998/09/24)',
        'j/n/y' => 'd/m/yy (24/9/98)',
        'd/m/y' => 'dd/mm/yy  (24/09/98)',
        'm/d/y' => 'm/d/yy  (9/24/98)',
        'm/d/Y' => 'm/d/yyyy  (9/24/1998)',
        'm-d-y' => 'mm-dd-yy  (09-24-98)',
        'm-d-Y' => 'mm-dd-yyyy  (09-24-1998)',
        'n-j-Y' => 'm-d-yyyy  (9-24-1998)',
        'n-j-y' => 'm-d-yy  (9-24-98)',
        'F d, Y' => 'Month dd, yyyy (September 24, 1998)',
        'dd-M-y' => 'dd-mon-yy (24-Sep-98)',
    );

    /**
     * Values which we skip
     *
     * @var array
     */
    protected $skipValues = array(
        ImportMapping::KEY_RESIDENT_ID => 'VACANT',
    );

    /**
     * @var ImportStorage
     */
    protected $storage;

    /**
     * @var CsvFileReaderImport
     */
    protected $reader;

    protected $geocoder;

    /**
     * @InjectParams({
     *     "storage"          = @Inject("accounting.import.storage"),
     *     "reader"           = @Inject("import.reader.csv"),
     *     "geocoder"         = @Inject("bazinga_geocoder.geocoder")
     * })
     */
    public function __construct(ImportStorage $storage, CsvFileReaderImport $reader, $geocoder)
    {
        $this->storage  = $storage;
        $this->reader   = $reader;
        $data = $this->storage->getImportData();
        $this->reader->setDelimiter($data[ImportStorage::IMPORT_FIELD_DELIMITER]);
        $this->reader->setEnclosure($data[ImportStorage::IMPORT_TEXT_DELIMITER]);
        $this->geocoder = $geocoder;
    }

    public function getFileData($offset = null, $rowCount = null)
    {
        $mappedData = array();
        $data       = $this->reader->read($this->storage->getFilePath(), $offset, $rowCount);

        foreach ($data as $key => $values) {
            $row          = $this->mappingRow($values);
            $mappedData[] = $row;
        }

        return $mappedData;
    }

    public function countLines()
    {
        return $this->reader->countLines($this->storage->getFilePath());
    }

    /**
     * $mappedData will be have next keys of array
     *
     *
     * Detailts about each key https://credit.atlassian.net/wiki/display/RT/Accounting+File+Import
     *
     * @return array
     */
    public function mappingRow(array $row)
    {
        $mapping    = $this->storage->getMapping();
        $mappedData = array();
        $countedFields = count($row);
        $data = array_values($row);
        for ($i = 1; $i <= $countedFields; $i++) {
            $indexData = $i-1;
            if (isset($mapping[$i]) && isset($data[$indexData])) {
                $mappedData[$mapping[$i]] = $data[$indexData];
            }
        }

        return $this->specificProcessingFields($this->makeSureAllKeysExist($mappedData));
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function specificProcessingFields($data)
    {
        $data[self::KEY_RENT] = str_replace(
            array(',',' '),
            '',
            $data[self::KEY_RENT]
        );

        if ($this->storage->isMultipleProperty()) {
            if (empty($data[self::KEY_UNIT])) {
                $data = $this->parseStreet($data);
            } else {
                $data = $this->parseUnit($data);
            }
        }

        return $data;
    }

    /**
     * Sometimes storage(specific situation when csv file not so good)
     * can return not correct array, so we need be sure, all key will exist
     *
     * @param array $mappedData
     * @return array
     */
    protected function makeSureAllKeysExist(array $mappedData)
    {
        if (empty($mappedData)) {
            return $mappedData;
        }

        foreach ($this->requiredKeysDefault as $requiredKey) {
            if (!isset($mappedData[$requiredKey])) {
                $mappedData[$requiredKey] = null;
            }
        }

        if ($this->storage->isMultipleProperty()) {
            foreach ($this->requiredKeysMultipleProperty as $requiredKey) {
                if (!isset($mappedData[$requiredKey])) {
                    $mappedData[$requiredKey] = null;
                }
            }
        }

        return $mappedData;
    }

    /**
     * @return array
     * @throws \RentJeeves\LandlordBundle\Exception\ImportMappingException
     */
    public function getDataForMapping()
    {
        $data = $this->reader->read($this->storage->getFilePath(), 0, 3);

        if (count($data) < 1) {
            throw new ImportMappingException('csv.file.too.small1');
        }

        if (!isset($data[1])) {
            throw new ImportMappingException('csv.file.too.small2');
        }

        return $data;
    }

    /**
     * Generate array values for view: 2 rows from csv file and choice  form field
     */
    public function prepareDataForCreateMapping(array $data)
    {
        $headers = array_keys($data[1]);
        $dataView = array();
        for ($i=1; $i < count($data[1])+1; $i++) {
            $dataView[] = array(
                'name' => $headers[$i-1],
                'row1' => $data[1][$headers[$i-1]],
                'row2' => (isset($data[2]) && isset($data[2][$headers[$i-1]]))? $data[2][$headers[$i-1]] : null,
                'form' => ImportMatchFileType::getFieldNameByNumber($i),
            );
        }

        return $dataView;
    }

    /**
     * Set mapping into session
     *
     * @param Form $form
     * @param array $data
     */
    public function setupMapping(Form $form, array $data)
    {
        $result = array();
        for ($i=1; $i < count($data[1])+1; $i++) {
            $nameField = ImportMatchFileType::getFieldNameByNumber($i);
            $value = $form->get($nameField)->getData();
            if ($value === ImportMatchFileType::EMPTY_VALUE) {
                continue;
            }

            $result[$i] = $value;
        }

        $this->storage->setMapping($result);
        $this->storage->setFileLine(0);
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    public function hasPaymentMapping(array $row)
    {
        if (!isset($row[self::KEY_PAYMENT_AMOUNT]) || !isset($row[self::KEY_PAYMENT_DATE])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    public function isSkipped(array $row)
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

    /**
     * @param Tenant $tenant
     * @param Contract $contract
     * @param ResidentMapping $residentMapping
     *
     * @return ContractWaiting
     */
    public function createContractWaiting(
        Tenant $tenant,
        Contract $contract,
        ResidentMapping $residentMapping
    ) {
        $waitingRoom = new ContractWaiting();
        $waitingRoom->setStartAt($contract->getStartAt());
        $waitingRoom->setFinishAt($contract->getFinishAt());
        $waitingRoom->setRent($contract->getRent());
        $waitingRoom->setIntegratedBalance($contract->getIntegratedBalance());
        /**
         * Property can be null because it can be not valid
         */
        if ($property = $contract->getProperty()) {
            $waitingRoom->setUnit($contract->getUnit());
            $waitingRoom->setProperty($property);
        }

        $waitingRoom->setFirstName($tenant->getFirstName());
        $waitingRoom->setLastName($tenant->getLastName());
        $waitingRoom->setGroup($contract->getGroup());

        $waitingRoom->setResidentId($residentMapping->getResidentId());

        return $waitingRoom;
    }

    /**
     * @param array $row
     *
     * @return Property
     */
    public function createProperty($row)
    {
        $property = new Property();
        $property->setCity($row[self::KEY_CITY]);
        $property->setStreet($row[self::KEY_STREET]);
        $property->setZip($row[self::KEY_ZIP]);
        $property->setArea($row[self::KEY_STATE]);
        if (empty($row[self::KEY_UNIT])) {
            $property->setIsSingle(true);
        }
        $result = $this->geocoder->using('google_maps')->geocode($property->getFullAddress());

        if (empty($result)) {
            return null;
        }

        return $property->parseGeocodeResponse($result);
    }

    protected function parseStreet($row)
    {
        preg_match('/(?:\#|unit)\s?([a-z0-9]{1,10})/is', $row[self::KEY_STREET], $matches);

        if (empty($matches)) {
            return $row;
        }
        list($unitString, $unitNumber) = $matches;

        $row[self::KEY_STREET] = str_replace($unitString, '', $row[self::KEY_STREET]);
        $row[self::KEY_UNIT] = $unitNumber;

        return $row;
    }

    /**
     * @param $row
     *
     * @return array
     */
    protected function parseUnit($row)
    {
        preg_match('/(?:\#|unit)?\s?([a-z0-9]{1,10})/is', $row[self::KEY_UNIT], $matches);

        if (empty($matches)) {
            return $row;
        }
        list($unitString, $unitNumber) = $matches;

        $row[self::KEY_STREET] = str_replace($unitString, '', $row[self::KEY_STREET]);
        $row[self::KEY_UNIT] = $unitNumber;

        return $row;
    }

    /**
     * @param $name
     *
     * @return array
     */
    public static function parseName($name)
    {
        $names = explode(' ', $name);

        switch (count($names)) {
            case 1:
                $data = array(
                    self::FIRST_NAME_TENANT => $names[0],
                    self::LAST_NAME_TENANT  => '',
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
                    self::LAST_NAME_TENANT  => implode(' ', array($names[2], $names[3])),
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
