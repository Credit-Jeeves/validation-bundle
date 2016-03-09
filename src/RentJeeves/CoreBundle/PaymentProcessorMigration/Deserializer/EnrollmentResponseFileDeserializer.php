<?php

namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer;

use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingResponseRecord;

/**
 * Service`s name "aci_enrollment_file_deserializer"
 */
class EnrollmentResponseFileDeserializer
{
    const ACCOUNT_TYPE_SYMBOL = 'A';
    const CONSUMER_TYPE_SYMBOL = 'C';
    const FUNDING_TYPE_SYMBOL = 'F';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @var array
     */
    protected $consumerResponseRecordOrder = [
        'recordType',
        'profileId',
        'consumerProfileId',
        'businessId',
        'userName',
        'password',
        'consumerFirstName',
        'consumerLastName',
        'primaryEmailAddress',
        'secondaryEmailAddress',
        'challengeQuestion1',
        'challengeAnswer1',
        'challengeQuestion2',
        'challengeAnswer2',
        'address1',
        'address2',
        'city',
        'state',
        'zipCode',
        'countryCode',
        'phoneNumber',
        'contactPhoneNumber',
        'textAddress',
        'status',
        'rejectReason',
    ];

    /**
     * @var array
     */
    protected $accountResponseRecordOrder = [
        'recordType',
        'profileId',
        'billingAccountId',
        'businessId',
        'billingAccountNumber',
        'nsfReturnCount',
        'nameOnBillingAccount',
        'billingAccountNickname',
        'address1',
        'address2',
        'city',
        'state',
        'zipCode',
        'countryCode',
        'paperBillOnOffFlag',
        'viewBillDetailFlag',
        'divisionId',
    ];

    /**
     * @var array
     */
    protected $fundingResponseRecordOrder = [
        'recordType',
        'profileId',
        'filer1',
        'fundingAccountId',
        'fundingAccountNickname',
        'fundingAccountType',
        'filer2',
        'fundingAccountHolderName',
        'fundingAccountHolderAddress1',
        'fundingAccountHolderAddress2',
        'fundingAccountHolderCity',
        'fundingAccountHolderState',
        'fundingAccountHolderZipCode',
        'fundingAccountHolderCountryCode',
        'fundingAccountPhoneNumber',
        'routingNumber',
        'bankAccountNumber',
        'bankAccountSubType',
        'cardExpirationMonth',
        'creditCardSubType',
        'cardExpirationYear',
        'cardNumber',
        'cardRoute'
    ];

    /**
     * @var string
     */
    protected $delimiter = '|';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @param string $pathToFile
     *
     * @return array
     */
    public function deserialize($pathToFile)
    {
        $this->logger->debug(sprintf('Start deserialize file "%s"', $pathToFile));
        $data = [];
        foreach ($this->getDataFromFile($pathToFile) as $key => $row) {
            switch ($row[0]) {
                case self::CONSUMER_TYPE_SYMBOL:
                    $this->logger->debug(sprintf('Creating ConsumerResponseRecord for %d row', $key));
                    $record = $this->rowToObject(
                        $row,
                        $this->consumerResponseRecordOrder,
                        new ConsumerResponseRecord()
                    );
                    break;
                case self::ACCOUNT_TYPE_SYMBOL:
                    $this->logger->debug(sprintf('Creating AccountResponseRecord for %d row', $key));
                    $record = $this->rowToObject(
                        $row,
                        $this->accountResponseRecordOrder,
                        new AccountResponseRecord()
                    );
                    break;
                case self::FUNDING_TYPE_SYMBOL:
                    $this->logger->debug(sprintf('Creating FundingResponseRecord for %d row', $key));
                    $record = $this->rowToObject(
                        $row,
                        $this->fundingResponseRecordOrder,
                        new FundingResponseRecord()
                    );
                    break;
                default:
                    continue 2;
            }
            $data[] = $record;
        }

        return $data;
    }

    /**
     * @param array $row
     * @param array $arrayOrder
     * @param ConsumerResponseRecord|FundingResponseRecord|AccountResponseRecord $object
     *
     * @return ConsumerResponseRecord|FundingResponseRecord|AccountResponseRecord
     */
    protected function rowToObject(array $row, array $arrayOrder, $object)
    {
        foreach ($arrayOrder as $key => $field) {
            $function = sprintf('set%s', ucfirst($field));
            if (false === method_exists($object, $function)) {
                throw new \LogicException(
                    sprintf(
                        '%s: method \'%s\' does not exists',
                        get_class($object),
                        $function
                    )
                );
            }

            $object->$function($row[$key]);
        }

        return $object;
    }

    /**
     * @param string $pathToFile
     *
     * @return array
     */
    protected function getDataFromFile($pathToFile)
    {
        if (false === file_exists($pathToFile) && false === is_readable($pathToFile)) {
            throw new \InvalidArgumentException('File not found or not readable');
        }
        $data = [];
        $file = fopen($pathToFile, 'r');
        while (feof($file) === false) {
            if (false !== $row = fgetcsv($file, 0, $this->delimiter, $this->enclosure)) {
                $data[] = $row;
            }
        }
        fclose($file);
        $this->logger->debug(sprintf('Got %d row(s) from file', count($data)));

        return $data;
    }
}
