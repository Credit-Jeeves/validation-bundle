<?php

namespace RentJeeves\LandlordBundle\Report;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Validator\DateWithFormat;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateValidator;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\Constraints\LengthValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\RegexValidator;
use Symfony\Component\Validator\Validator;

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

    const KEY_UNIT = 'unit';

    const KEY_RESIDENT_ID = 'resident_id';

    const KEY_TENANT_NAME = 'tenant_name';

    const KEY_RENT = 'rent';

    const KEY_MOVE_IN = 'move_in';

    const KEY_MOVE_OUT = 'move_out';

    const KEY_LEASE_END = 'lease_end';

    const KEY_BALANCE = 'balance';

    const KEY_EMAIL = 'email';

    const KEY_IS_VALID = 'isValid';

    const KEY_ERRORS = 'errors';

    protected $session;

    protected $reader;

    protected $validator;

    protected $rowsErrorsList = array();

    protected $mapping = array();

    /**
     * @InjectParams({
     *     "session"   = @Inject("session"),
     *     "reader"    = @Inject("reader.csv"),
     *     "validator" = @Inject("validator")
     * })
     */
    public function __construct(Session $session, CsvFileReader $reader, Validator $validator)
    {
        $this->session = $session;
        $this->reader  = $reader;
        $data          = $this->getImportData();
        $this->reader->setDelimiter($data[self::IMPORT_FIELD_DELIMITER]);
        $this->reader->setEnclosure($data[self::IMPORT_TEXT_DELIMITER]);
        $this->validator = $validator;
    }

    public function setFilePath($fileName)
    {
        $this->session->set(self::IMPORT_FILE_PATH, $fileName);
    }

    public function setPropertyId($propertyId)
    {
        $this->session->set(self::IMPORT_PROPERTY_ID, $propertyId);
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

        $mappedData[self::KEY_IS_VALID] = true;
        $mappedData[self::KEY_ERRORS] = array();

        return $mappedData;
    }

    /**
     * return boolean
     */
    public function isValidCsvData()
    {
        $data       = $this->getImportData();
        $data       = $this->reader->read($data[self::IMPORT_FILE_PATH]);
        $mappedData = array();
        $isValid = true;
        foreach ($data as $key => $values) {
            $row          = $this->mappingRow($values);
            $isValidRow   = $this->isValidateRow($row);
            if (!$isValid) {
                $row[self::KEY_IS_VALID] = false;
                $row[self::KEY_ERRORS] = $this->rowsErrorsList;
            }

            $mappedData[] = $row;
            unset($data[$key]);
        }
    }

    public function isValidateRow($row)
    {
        $this->rowsErrorsList = array();

        $this->validateValue(
            $row,
            array(
                new EmailValidator(),
                new NotBlank(),
            ),
            self::KEY_EMAIL
        );

        $this->isValidateValue(
            $row,
            array(
                new LengthValidator(
                    array(
                        'min' => 1,
                        'max' => 50
                    )
                ),
                new RegexValidator(
                    array(
                        'pattern' => '/^[A-Za-z_\-0-9-\s]{1,50}+$/'
                    )
                ),
                new NotBlank(),
            ),
            self::KEY_UNIT
        );

        $this->isValidateRow(
            $row,
            array(
                new NotBlank(),
                new RegexValidator(
                    array(
                        'pattern' => '/^[A-Za-z_\-0-9-\s]{1,100}+$/'
                    )
                ),
            ),
            self::KEY_TENANT_NAME
        );

        $this->isValidateRow(
            $row,
            array(
                new NotBlank(),
                new RegexValidator(
                    array(
                        '/^[0-9]+(\.[0-9][0-9])?+$/'
                    )
                )
            ),
            self::KEY_BALANCE
        );

        $this->isValidateRow(
            $row,
            array(
                new NotBlank(),
                new RegexValidator(
                    array(
                        '/^[0-9]+(\.[0-9][0-9])?+$/'
                    )
                ),
            ),
            self::KEY_RENT
        );

        $this->isValidateRow(
            $row,
            array(
                new NotBlank(),
                new DateWithFormat(),
            ),
            self::KEY_MOVE_IN
        );

        $this->isValidateRow(
            $row,
            array(
                new NotBlank(),
                new DateWithFormat(),
            ),
            self::KEY_LEASE_END
        );

        if (!empty($row[self::KEY_MOVE_OUT])) {
            $this->isValidateRow(
                $row,
                array(
                    new DateWithFormat(),
                ),
                self::KEY_MOVE_OUT
            );
        }

        if (empty($this->rowsErrorsList)) {
            return true;
        }

        return false;
    }

    protected function isValidateValue(array $values, array $validators, $key)
    {
        foreach ($validators as $validator) {
            $errors = $this->validator->validateValue(
                $values[$key],
                $validator
            );

            if (count($errors) > 0) {
                $this->rowsErrorsList[$key][] = $errors;
            }
        }
    }
}
