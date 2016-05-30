<?php

namespace RentJeeves\ImportBundle\LeaseImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportLease;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\DataBundle\Enum\ImportLeaseResidentStatus;
use RentJeeves\DataBundle\Enum\ImportLeaseStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\ImportBundle\Exception\ImportTransformerException;
use RentJeeves\ImportBundle\Helper\LeaseEndDateCalculator;
use RentJeeves\ImportBundle\Helper\ShortNameFetcher;

/**
 * Service`s name "import.lease.transformer.csv"
 */
class CsvTransformer implements TransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $requiredMappingFields = [
        'unit_id', 'tenant_name', 'balance', 'lease_end', 'move_in', 'rent', 'move_out', 'email'
    ];

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        $group = $import->getGroup();
        $this->logger->info(
            sprintf(
                'Started CSV transform data for Import#%d.',
                $import->getId()
            ),
            ['group' => $group]
        );

        if (empty($accountingSystemData['hashHeader'])) {
            throw new ImportTransformerException('Input array should contain not empty "hashHeader".');
        }

        if (null === $importMapping = $this->findImportMapping($group, $accountingSystemData['hashHeader'])) {
            $message = sprintf(
                'Group#%d doesn`t have importMapping for hash = "%s"',
                $group->getId(),
                $accountingSystemData['hashHeader']
            );
            $this->logger->warning($message, ['group' => $group]);

            throw new ImportTransformerException($message);
        }

        $importMappingRule = $this->getImportMappingRule($importMapping);

        foreach ($accountingSystemData['data'] as $accountingSystemRecord) {
            $importLease = new ImportLease();
            $importLease->setImport($import);
            $this->em->persist($importLease);

            try {
                $this->transformRecord($importLease, $accountingSystemRecord, $importMappingRule);
            } catch (ImportTransformerException $e) {
                $importLease->setLeaseStatus(ImportLeaseStatus::ERROR);
                $importLease->setErrorMessages([$e->getMessage()]);
            }
        }

        $this->em->flush();
    }

    /**
     * @param ImportLease $importLease
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     */
    protected function transformRecord(ImportLease $importLease, array $accountingSystemRecord, array $importMappingRule)
    {
        $importSettings = $importLease->getImport()->getGroup()->getImportSettings();
        $dateFormat = $importSettings->getCsvDateFormat();

        $importLease->setRent($this->getRent($accountingSystemRecord, $importMappingRule));
        $importLease->setExternalLeaseId($this->getExternalLeaseId($accountingSystemRecord, $importMappingRule));
        $importLease->setExternalPropertyId($this->getExternalPropertyId($accountingSystemRecord, $importMappingRule));
        $importLease->setIntegratedBalance($this->getIntegratedBalance($accountingSystemRecord, $importMappingRule));
        $importLease->setExternalResidentId($this->getExternalResidentId($accountingSystemRecord, $importMappingRule));
        $importLease->setTenantEmail($this->getTenantEmail($accountingSystemRecord, $importMappingRule));
        $importLease->setPhone($this->getPhone($accountingSystemRecord, $importMappingRule));
        $importLease->setExternalUnitId($this->getExternalUnitId($accountingSystemRecord, $importMappingRule));
        $importLease->setPaymentAccepted($this->getPaymentAccepted($accountingSystemRecord, $importMappingRule));
        $importLease->setFirstName($this->getFirstName($accountingSystemRecord, $importMappingRule));
        $importLease->setLastName($this->getLastName($accountingSystemRecord, $importMappingRule));
        $importLease->setStartAt($this->getStartAt($accountingSystemRecord, $importMappingRule, $dateFormat));
        $importLease->setFinishAt($this->getFinishAt($accountingSystemRecord, $importMappingRule, $dateFormat));
        $importLease->setDueDate($this->getDueDate($importLease));
        $importLease->setResidentStatus(
            $this->getResidentStatus($accountingSystemRecord, $importMappingRule, $dateFormat)
        );
        $importLease->setGroup($this->getGroup($importLease, $accountingSystemRecord, $importMappingRule));
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getRent(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'rent');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getExternalLeaseId(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'external_lease_id');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getExternalPropertyId(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'external_property_id');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getIntegratedBalance(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'balance');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getExternalResidentId(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'resident_id');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getTenantEmail(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'email');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getPhone(array $accountingSystemRecord, array $importMappingRule)
    {
        return $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'user_phone');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getExternalUnitId(array $accountingSystemRecord, array $importMappingRule)
    {
        return  $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'unit_id');
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return int|null|string
     */
    protected function getPaymentAccepted(array $accountingSystemRecord, array $importMappingRule)
    {
        $paymentAccepted = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'payment_accepted');

        if (empty($paymentAccepted)) {
            return null;
        }

        if (strtolower($paymentAccepted) === 'y') {
            return PaymentAccepted::ANY;
        }

        if (strtolower($paymentAccepted) === 'n') {
            return PaymentAccepted::DO_NOT_ACCEPT;
        }

        return $paymentAccepted;
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getFirstName(array $accountingSystemRecord, array $importMappingRule)
    {
        $tenantName = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'tenant_name');

        return ShortNameFetcher::extractFirstName($tenantName);
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return null|string
     */
    protected function getLastName(array $accountingSystemRecord, array $importMappingRule)
    {
        $tenantName = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'tenant_name');

        return ShortNameFetcher::extractLastName($tenantName);
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @param string $dateFormat
     * @return \DateTime|null
     */
    protected function getStartAt(array $accountingSystemRecord, array $importMappingRule, $dateFormat)
    {
        $moveIn = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'move_in');

        return $this->convertDateStringToObjectDate($moveIn, $dateFormat);
    }


    /**
     * @param ImportLease $importLease
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @return Group|null
     * @throws ImportTransformerException
     * @throws \Exception
     */
    protected function getGroup(ImportLease $importLease, array $accountingSystemRecord, array $importMappingRule)
    {
        $accountNumber = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'group_account_number');
        $group = $importLease->getImport()->getGroup();

        if (empty($accountNumber)) {
            return $group;
        }

        $this->logger->debug('Look up group by account number:' . $accountNumber);

        try {
            $group = $this->em->getRepository('DataBundle:Group')->getGroupByAccountNumber(
                $accountNumber,
                $group->getHolding()
            );
        } catch(\LogicException $e) {
            throw new ImportTransformerException($e->getMessage());
        }

        if (empty($group)) {
            throw new ImportTransformerException(
                'We don\'t have group for import lease entity importId#' . $importLease->getImport()->getId()
            );
        }

        return $group;
    }


    /**
     * @param ImportLease $importLease
     * @return int
     */
    protected function getDueDate(ImportLease $importLease)
    {
        $groupSettings = $importLease->getImport()->getGroup()->getGroupSettings();

        return $groupSettings->getDueDate();
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @param string $dateFormat
     * @return \DateTime|null
     */
    protected function getFinishAt(array $accountingSystemRecord, array $importMappingRule, $dateFormat)
    {
        $leaseEnd = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'lease_end');
        $moveOut = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'move_out');
        $tenantStatus = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'tenant_status');
        $mtm = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'month_to_month');

        $leaseEnd = $this->convertDateStringToObjectDate($leaseEnd, $dateFormat);
        $moveOut = $this->convertDateStringToObjectDate($moveOut, $dateFormat);

        return LeaseEndDateCalculator::calculateFinishAt($leaseEnd, $moveOut, $tenantStatus, $mtm);
    }

    /**
     * @param $dateString
     * @param $format
     * @return \DateTime|null
     */
    protected function convertDateStringToObjectDate($dateString, $format)
    {
        if (empty($dateString)) {
            return null;
        }

        $date = \DateTime::createFromFormat($format, $dateString);

        if ($date instanceof \DateTime) {
            $date->setTime(0, 0, 0);

            return $date;
        }

        throw new ImportTransformerException(
            sprintf(
                'Failed to convert dateSting %s to DateTime object by format %s.',
                $dateString,
                $format
            )
        );
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @param string $dateFormat
     * @return string
     */
    protected function getResidentStatus(
        array $accountingSystemRecord,
        array $importMappingRule,
        $dateFormat
    ) {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $moveOut = $this->getFieldValueByKey($accountingSystemRecord, $importMappingRule, 'move_out');
        $moveOut = $this->convertDateStringToObjectDate($moveOut, $dateFormat);
        // only finish the contract if MoveOut is today or earlier
        if ($moveOut !== null && $moveOut <= $today) {
            return ImportLeaseResidentStatus::PAST;
        }

        return ImportLeaseResidentStatus::CURRENT;
    }

    /**
     * @param array $accountingSystemRecord
     * @param array $importMappingRule
     * @param string $fieldName
     * @return null|string
     */
    protected function getFieldValueByKey(array $accountingSystemRecord, array $importMappingRule, $fieldName)
    {
        $key = array_key_exists($fieldName, $importMappingRule) ? $importMappingRule[$fieldName] : null;

        if ($key === null) {
            $this->logger->debug(sprintf('We don\'t have fieldName(%s) in MappingRule', $fieldName));

            return null;
        }

        $value = $accountingSystemRecord[$key];

        if (empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @param ImportMappingChoice $importMappingChoice
     * @return array
     * @throws ImportTransformerException
     */
    protected function getImportMappingRule(ImportMappingChoice $importMappingChoice)
    {
        $mappingData = $importMappingChoice->getMappingData();
        $mappingRule = array_flip($mappingData);
`
        $missingFields = [];
        foreach ($this->requiredMappingFields as $requiredMappingField) {
            if (false === isset($mappingRule[$requiredMappingField])) {
                $missingFields[] = $requiredMappingField;
            }
        }

        if (false === empty($missingFields)) {
            $message = sprintf(
                'ImportMapping doesn`t contain mapping for required field(s): %s',
                implode(', ', $missingFields)
            );
            $this->logger->warning($message, ['group' => $importMappingChoice->getGroup()]);

            throw new ImportTransformerException($message);
        }

        $importMappingRule = [];
        foreach ($mappingRule as $key => $value) {
            $importMappingRule[$key] = $value - 1;
        }

        return $importMappingRule;
    }

    /**
     * @param Group  $group
     * @param string $headerHash
     *
     * @return \RentJeeves\DataBundle\Entity\ImportMappingChoice
     */
    protected function findImportMapping(Group $group, $headerHash)
    {
        return $this->em->getRepository('RjDataBundle:ImportMappingChoice')->findOneBy(
            [
                'group' => $group,
                'headerHash' => $headerHash,
            ]
        );
    }
}
