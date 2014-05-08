<?php

namespace RentJeeves\LandlordBundle\Accounting;

use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.storage")
 */
class ImportStorage
{
    const IS_MULTIPLE_PROPERTY = 'is_multiple_property';

    const IMPORT_FILE_PATH = 'importFileName';

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    const IMPORT_TEXT_DELIMITER = 'importTextDelimiter';

    const IMPORT_FIELD_DELIMITER = 'importFieldDelimiter';

    const IMPORT_FILE_LINE = 'importFileLine';

    const IMPORT_MAPPING = 'importMapping';

    const IMPORT_DATE_FORMAT = 'importDateFormat';

    protected $session;

    protected $em;

    /**
     * @InjectParams({
     *     "session"          = @Inject("session")
     * })
     */
    public function __construct(Session $session)
    {
        $this->session     = $session;
    }

    public function setDateFormat($format)
    {
        if (!isset(ImportMapping::$mappingDates[$format])) {
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

    public function getFilePath()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->session->get(self::IMPORT_FILE_PATH);
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

    public function setMapping(array $mappedData)
    {
        $this->session->set(self::IMPORT_MAPPING, json_encode($mappedData));
    }

    public function getMapping()
    {
        if (empty($this->mapping)) {
            $this->mapping = json_decode($this->session->get(self::IMPORT_MAPPING), true);
        }

        return $this->mapping;
    }

    /**
     * @return array
     * @throws \RentJeeves\LandlordBundle\Exception\ImportStorageException
     */
    public function getImportData()
    {
        $data = array(
            self::IMPORT_FILE_PATH       => $this->session->get(self::IMPORT_FILE_PATH),
            self::IMPORT_PROPERTY_ID     => $this->session->get(self::IMPORT_PROPERTY_ID),
            self::IMPORT_TEXT_DELIMITER  => $this->session->get(self::IMPORT_TEXT_DELIMITER),
            self::IMPORT_FIELD_DELIMITER => $this->session->get(self::IMPORT_FIELD_DELIMITER),
        );

        foreach ($data as $key => $value) {
            if (empty($value)) {
                throw new ImportStorageException("Value for {$key} does not set");
            }
        }

        $data[self::IMPORT_MAPPING] = $this->session->get(self::IMPORT_MAPPING);
        if (!empty($data[self::IMPORT_MAPPING])) {
            $data[self::IMPORT_MAPPING] = json_decode($data[self::IMPORT_MAPPING], true);
        }
        $data[self::IMPORT_FILE_PATH] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $data[self::IMPORT_FILE_PATH];

        return $data;
    }
}
