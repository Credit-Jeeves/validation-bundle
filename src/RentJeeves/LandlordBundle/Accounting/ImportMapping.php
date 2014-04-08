<?php

namespace RentJeeves\LandlordBundle\Accounting;

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

    /**
     * @InjectParams({
     *     "storage"          = @Inject("accounting.import.storage"),
     *     "reader"           = @Inject("import.reader.csv")
     * })
     */
    public function __construct(ImportStorage $storage, CsvFileReaderImport $reader)
    {
        $this->storage  = $storage;
        $this->reader   = $reader;
        $data = $this->storage->getImportData();
        $this->reader->setDelimiter($data[ImportStorage::IMPORT_FIELD_DELIMITER]);
        $this->reader->setEnclosure($data[ImportStorage::IMPORT_TEXT_DELIMITER]);
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
            if (isset($mapping[$i])) {
                $indexData = $i-1;
                $mappedData[$mapping[$i]] = $data[$indexData];
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

        if (count($data[1]) < 8) {
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
                'row2' => (isset($data[2]))? $data[2][$headers[$i-1]] : null,
                'form' => ImportMatchFileType::getFieldNameByNumber($i),
            );
        }

        return $dataView;
    }

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

    public function isHavePaymentMapping($row)
    {
        if (!isset($row[self::KEY_PAYMENT_AMOUNT]) && !isset($row[self::KEY_PAYMENT_DATE])) {
            return false;
        }

        return true;
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
