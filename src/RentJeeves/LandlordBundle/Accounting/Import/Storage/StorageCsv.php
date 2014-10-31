<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.storage.csv")
 */
class StorageCsv extends StorageAbstract
{
    const IS_MULTIPLE_PROPERTY = 'is_multiple_property';

    const IMPORT_FILE_PATH = 'importFileName';

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    const IMPORT_TEXT_DELIMITER = 'importTextDelimiter';

    const IMPORT_FIELD_DELIMITER = 'importFieldDelimiter';

    const IMPORT_DATE_FORMAT = 'importDateFormat';

    /**
     * @InjectParams({
     *     "session" = @Inject("session")
     * })
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function setDateFormat($format)
    {
        if (!isset(Mapping::$mappingDates[$format])) {
            throw new ImportStorageException("Not supported format {$format}");
        }
        $this->session->set(self::IMPORT_DATE_FORMAT, $format);
    }

    public function setIsMultipleProperty($isMultipleProperty = true)
    {
        $this->session->set(self::IS_MULTIPLE_PROPERTY, $isMultipleProperty);
    }

    public function isMultipleProperty()
    {
        return $this->session->get(self::IS_MULTIPLE_PROPERTY);
    }

    public function getDateFormat()
    {
        return $this->session->get(self::IMPORT_DATE_FORMAT);
    }

    public function setFilePath($fileName)
    {
        $this->session->set(self::IMPORT_FILE_PATH, $fileName);
    }

    public function getFilePath($justFileName = false)
    {
        if ($justFileName) {
            return $this->session->get(self::IMPORT_FILE_PATH, null);
        }
        return $this->getFileDirectory().$this->session->get(self::IMPORT_FILE_PATH, '');
    }


    public function setPropertyId($propertyId)
    {
        $this->session->set(self::IMPORT_PROPERTY_ID, $propertyId);
    }

    public function getPropertyId()
    {
        if ($this->isMultipleProperty()) {
            return null;
        }
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

    public function getTextDelimiter()
    {
        $this->session->get(self::IMPORT_TEXT_DELIMITER);
    }

    public function getFieldDelimiter()
    {
        $this->session->get(self::IMPORT_FIELD_DELIMITER);
    }


    /**
     * @param FormInterface $form
     */
    public function setImportData(FormInterface $form)
    {
        $file = $form['attachment']->getData();
        $property = $form['property']->getData();
        $textDelimiter = $form['textDelimiter']->getData();
        $fieldDelimiter = $form['fieldDelimiter']->getData();
        $dateFormat = $form['dateFormat']->getData();
        $tmpDir = sys_get_temp_dir();
        $newFileName = uniqid() . '.csv';
        $file->move($tmpDir, $newFileName);

        $this->setFieldDelimiter($fieldDelimiter);
        $this->setTextDelimiter($textDelimiter);
        $this->setFilePath($newFileName);
        if ($property instanceof Property) {
            $this->setPropertyId($property->getId());
            $this->setIsMultipleProperty(false);
        } else {
            $this->setIsMultipleProperty(true);
        }
        $this->setDateFormat($dateFormat);
    }

    /**
     * @return array
     * @throws \RentJeeves\LandlordBundle\Exception\ImportStorageException
     */
    public function getImportData()
    {
        $data = array(
            self::IMPORT_FILE_PATH => $this->session->get(self::IMPORT_FILE_PATH),
            self::IMPORT_TEXT_DELIMITER => $this->session->get(self::IMPORT_TEXT_DELIMITER),
            self::IMPORT_FIELD_DELIMITER => $this->session->get(self::IMPORT_FIELD_DELIMITER),
        );

        if (!$this->isMultipleProperty()) {
            $data[self::IMPORT_PROPERTY_ID] = $this->getPropertyId();
        }

        foreach ($data as $key => $value) {
            if (empty($value)) {
                throw new ImportStorageException("Value for {$key} does not set");
            }
        }

        $data[self::IMPORT_MAPPING] = $this->session->get(self::IMPORT_MAPPING);
        if (!empty($data[self::IMPORT_MAPPING])) {
            $data[self::IMPORT_MAPPING] = json_decode($data[self::IMPORT_MAPPING], true);
        }
        $data[self::IMPORT_FILE_PATH] = $this->getFileDirectory(). $data[self::IMPORT_FILE_PATH];

        return $data;
    }

    public function getFileDirectory()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    public function isValid()
    {
        try {
            $data = $this->getImportData();
        } catch (ImportStorageException $e) {
            return false;
        }

        if (empty($data[StorageAbstract::IMPORT_MAPPING])) {
            return false;
        }

        return true;
    }

    public function clearSession()
    {
        $this->removeFile();
        $this->session->remove(self::IMPORT_DATE_FORMAT);
        $this->session->remove(self::IMPORT_PROPERTY_ID);
        $this->session->remove(self::IMPORT_FIELD_DELIMITER);
        $this->session->remove(self::IMPORT_FILE_PATH);
        $this->session->remove(self::IMPORT_TEXT_DELIMITER);
        $this->session->remove(self::IMPORT_MAPPING);
        $this->session->remove(self::IMPORT_STORAGE_TYPE);
        $this->session->remove(self::IMPORT_OFFSET_START);
    }

    public function clearDataBeforeReview()
    {
    }

    protected function removeFile()
    {
        if (file_exists($this->getFilePath()) && !is_dir($this->getFilePath())) {
            unlink($this->getFilePath());
        }
    }
}
