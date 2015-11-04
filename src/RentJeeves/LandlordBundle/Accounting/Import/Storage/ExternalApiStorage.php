<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use Symfony\Component\Form\FormInterface;
use RentJeeves\DataBundle\Entity\ImportApiMapping;

class ExternalApiStorage extends StorageCsv
{
    /**
     * @var array
     */
    protected $defaultMapping = [
        1 => Mapping::KEY_RESIDENT_ID,
        2 => Mapping::KEY_UNIT,
        3 => Mapping::KEY_MOVE_IN,
        4 => Mapping::KEY_LEASE_END,
        5 => Mapping::KEY_RENT,
        6 => Mapping::FIRST_NAME_TENANT,
        7 => Mapping::LAST_NAME_TENANT,
        8 => Mapping::KEY_EMAIL,
        9 => Mapping::KEY_MOVE_OUT,
        10 => Mapping::KEY_BALANCE,
        11 => Mapping::KEY_MONTH_TO_MONTH,
        12 => Mapping::KEY_PAYMENT_ACCEPTED,
        13 => Mapping::KEY_EXTERNAL_LEASE_ID
    ];

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    const IMPORT_EXTERNAL_PROPERTY_ID = 'importExternalPropertyId';

    const IMPORT_IS_LOADED = 'importIsLoaded';

    const DATE_FORMAT = 'Y-m-d';

    const FIELD_DELIMITER = ',';

    const TEXT_DELIMITER = '"';

    /**
     * @return ImportApiMapping
     */
    protected function getImportApiMapping()
    {
        return $this->em->getRepository('RjDataBundle:ImportApiMapping')->findOneBy(
            [
                'externalPropertyId' => $this->getImportExternalPropertyId(),
                'holding' => $this->getLandlord()->getHolding()
            ]
        );
    }

    /**
     * @return array|bool
     */
    protected function getMappingFromDB()
    {
        $importApiMapping = $this->getImportApiMapping();

        if (empty($importApiMapping)) {
            return false;
        }

        $data = $importApiMapping->getMappingData();

        if (empty($data)) {
            return false;
        }

        return $data;
    }

    public function setImportPropertyId($propertyId)
    {
        $this->session->set(self::IMPORT_PROPERTY_ID, $propertyId);
    }

    public function getImportPropertyId()
    {
        return $this->session->get(self::IMPORT_PROPERTY_ID);
    }

    public function setImportExternalPropertyId($propertyId)
    {
        $this->session->set(self::IMPORT_EXTERNAL_PROPERTY_ID, $propertyId);
    }

    public function getImportExternalPropertyId()
    {
        return $this->session->get(self::IMPORT_EXTERNAL_PROPERTY_ID);
    }

    public function setImportLoaded($importLoaded)
    {
        $this->session->set(self::IMPORT_IS_LOADED, $importLoaded);
    }

    public function getImportLoaded()
    {
        return $this->session->get(self::IMPORT_IS_LOADED);
    }

    /**
     * @param FormInterface $form
     */
    public function setImportData(FormInterface $form)
    {
        $property = $form['property']->getData();
        $importType = $form['importType']->getData();

        if ($property instanceof Property && ImportType::SINGLE_PROPERTY === $importType) {
            $this->setImportPropertyId($property->getId());
            $this->setIsMultipleProperty(false);
            $this->setImportType(ImportType::SINGLE_PROPERTY);
        } else {
            $this->setIsMultipleProperty(true);
            $this->setImportType(ImportType::MULTI_PROPERTIES);
        }
        $this->setImportExternalPropertyId($form['propertyId']->getData());
        $this->setOnlyException($form['onlyException']->getData());
        $this->setImportLoaded(false);
        $this->setMapping($this->getImportData());

    }

    /**
     * @return array
     * @throws \RentJeeves\LandlordBundle\Exception\ImportStorageException
     */
    public function getImportData()
    {
        $externalApiData = array(
            self::IMPORT_PROPERTY_ID => $this->getImportPropertyId(),
            self::IMPORT_EXTERNAL_PROPERTY_ID => $this->getImportExternalPropertyId(),
            self::IMPORT_ONLY_EXCEPTION => $this->isOnlyException(),
        );

        if ($this->getImportLoaded()) {
            $csvData = parent::getImportData();
            $externalApiData = array_merge($csvData, $externalApiData);
        }

        return $externalApiData;
    }

    public function isMultipleProperty()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->getImportLoaded()) {
            return parent::isValid();
        }

        try {
            $data = $this->getImportData();
        } catch (ImportStorageException $e) {
            return false;
        }

        if (empty($data[self::IMPORT_EXTERNAL_PROPERTY_ID])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $array
     */
    protected function writeCsvToFile(array $array)
    {
        $array = $this->overrideValuesByImportApiMapping($array);
        $filePath = $this->getFilePath(true);
        if (empty($filePath)) {
            $newFileName = uniqid() . '.csv';
            $this->setFilePath($newFileName);
        }
        $out = fopen($this->getFilePath(), 'a');
        fputcsv($out, $array, self::FIELD_DELIMITER, self::TEXT_DELIMITER);
        fclose($out);
    }

    public function clearSession()
    {
        $this->session->remove(self::IMPORT_EXTERNAL_PROPERTY_ID);
        $this->session->remove(self::IMPORT_PROPERTY_ID);
        $this->session->remove(self::IMPORT_IS_LOADED);
        $this->session->remove(self::IMPORT_ONLY_EXCEPTION);
        parent::clearSession();
    }

    public function clearDataBeforeReview()
    {
        $this->session->remove(self::IMPORT_OFFSET_START);
        $this->session->remove(self::IMPORT_FILE_PATH);
        $this->getImportLoaded(false);
        parent::clearDataBeforeReview();
    }

    /**
     * @param  string|\DateTime $date
     * @return string
     */
    protected function getDateString($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        return $date;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function overrideValuesByImportApiMapping(array $data)
    {
        $mappingApiMapping = $this->getImportApiMapping();

        if (!$mappingApiMapping) {
            return $data;
        }

        $currentMapping = $this->getCurrentMapping();

        foreach ($currentMapping as $number => $value) {
            $number -= 1;
            $this->overrideValueInArray($value, Mapping::KEY_CITY, $number, $mappingApiMapping->getCity(), $data);
            $this->overrideValueInArray($value, Mapping::KEY_ZIP, $number, $mappingApiMapping->getZip(), $data);
            $this->overrideValueInArray($value, Mapping::KEY_STATE, $number, $mappingApiMapping->getState(), $data);
            $this->overrideValueInArray($value, Mapping::KEY_STREET, $number, $mappingApiMapping->getStreet(), $data);
        }

        return $data;
    }

    /**
     * @param string $currentKey
     * @param string $checkedKey
     * @param string $keyOfMainData
     * @param string $newValue
     * @param array $data
     */
    protected function overrideValueInArray($currentKey, $checkedKey, $keyOfMainData, $newValue, &$data)
    {
        if (empty($newValue)) {
            return;
        }

        if ($currentKey == $checkedKey) {
            $data[$keyOfMainData] = $newValue;
        }
    }

    /**
     * @return array
     * @throws ImportStorageException
     */
    protected function getCurrentMapping()
    {
        if (!$mappingFromDb = $this->getMappingFromDB()) {
            return $this->defaultMapping;
        }

        $this->assertMapping($this->defaultMapping, $mappingFromDb, get_class($this));

        return $mappingFromDb;
    }

    /**
     * @{inheritdoc}
     */
    protected function initializeParameters()
    {
        $this->setFieldDelimiter(self::FIELD_DELIMITER);
        $this->setTextDelimiter(self::TEXT_DELIMITER);
        $this->setDateFormat(self::DATE_FORMAT);

        $mapping = $this->getCurrentMapping();
        $this->writeCsvToFile($mapping);
        $this->setMapping($mapping);
    }

    /**
     * @param array $defaultMapping
     * @param array $dbMapping
     * @param string $system
     */
    public function assertMapping(array $defaultMapping, array $dbMapping, $system)
    {
        $countDefault = count($defaultMapping);
        $countDB = count($dbMapping);

        if ($countDB !== $countDefault) {
            throw new \LogicException(
                sprintf(
                    'Mapping which we specify on DB wrong for %s. Count of elements should be equals. %s != %s',
                    $system,
                    $countDefault,
                    $countDB
                )
            );
        }
    }
}
