<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
use Symfony\Component\Form\Form;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class MappingCsv extends MappingAbstract
{
    /**
     * Values which we skip
     *
     * @var array
     */
    protected $skipValues = array(
        MappingCsv::KEY_RESIDENT_ID => 'VACANT',
    );

    /**
     * @var CsvFileReaderImport
     */
    protected $reader;

    public function __construct(StorageCsv $storage, CsvFileReaderImport $reader)
    {
        $this->storage  = $storage;
        $this->reader   = $reader;
        $data = $this->storage->getImportData();
        $this->reader->setDelimiter($data[StorageCsv::IMPORT_FIELD_DELIMITER]);
        $this->reader->setEnclosure($data[StorageCsv::IMPORT_TEXT_DELIMITER]);
    }

    public function isNeedManualMapping()
    {
        return true;
    }

    public function getTotal()
    {
        return $this->reader->countLines($this->storage->getFilePath());
    }

    /**
     * $mappedData will be have next keys of array
     *
     * Details about each key https://credit.atlassian.net/wiki/display/RT/Accounting+File+Import
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
        foreach (array(self::KEY_RENT, self::KEY_BALANCE) as $key) {
            $data[$key] = str_replace(
                array(',',' '),
                '',
                $data[$key]
            );
        }

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
        $mappedData = parent::makeSureAllKeysExist($mappedData);

        if ($this->storage->isMultipleGroup()) {
            if (!isset($mappedData[self::KEY_GROUP_ACCOUNT_NUMBER])) {
                $mappedData[self::KEY_GROUP_ACCOUNT_NUMBER] = null;
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
     * @param Group $group
     */
    public function setupMapping(Form $form, array $data, Group $group)
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

        $headerHash = self::getHeaderFileHash($data);
        $this->saveMapping($group, $result, $headerHash);

        $this->storage->setMapping($result);
        $this->storage->setOffsetStart(0);
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
     * @param $data
     *
     * @return string
     */
    public static function getHeaderFileHash(array $data)
    {
        return md5(implode(array_keys($data[1])));
    }

    /**
     * @param $headerHash
     * @param Group $group
     * @return ImportMappingChoice|null
     */
    public function getSelectedImportMapping($headerHash, Group $group)
    {
        return $this->em
            ->getRepository('RjDataBundle:ImportMappingChoice')
            ->findOneBy(['headerHash' => $headerHash, 'group' => $group]);
    }

    /**
     * @param Group $group
     * @param array $data
     */
    protected function saveMapping(Group $group, $data, $headerHash)
    {
        $importMappingChoice = $this->getSelectedImportMapping($headerHash, $group);

        if (!$importMappingChoice) {
            $importMappingChoice = new ImportMappingChoice();
            $importMappingChoice->setHeaderHash($headerHash);
            $importMappingChoice->setGroup($group);
        }
        $importMappingChoice->setMappingData($data);
        $this->em->persist($importMappingChoice);
        $this->em->flush($importMappingChoice);
    }
}
