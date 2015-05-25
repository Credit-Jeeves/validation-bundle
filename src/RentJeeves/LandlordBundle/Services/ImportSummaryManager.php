<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\ImportError;
use RentJeeves\DataBundle\Entity\ImportSummary;
use RentJeeves\DataBundle\Enum\ImportType;
use Psr\Log\LoggerInterface;

/**
 * @Service("import_summary.manager")
 */
class ImportSummaryManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ImportSummary
     */
    protected $importSummaryModel;

    /**
     * @InjectParams({
     *     "em" = @Inject("doctrine.orm.entity_manager"),
     *     "logger" = @Inject("logger")
     * })
     */
    public function __construct(EntityManager $em, LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger ? $logger : new Logger(get_class());
    }

    /**
     * @param Group   $group
     * @param string  $importType
     * @param integer $publicId
     */
    public function initialize(Group $group, $importType, $publicId = null)
    {
        if (!empty($this->importSummaryModel)) {
            return;
        }

        $this->logger->debug('Initialize import summary report');
        if (!ImportType::isValid($importType)) {
            ImportType::throwsInvalid($importType);
        }

        if (!empty($publicId)) {
            $this->importSummaryModel = $this->findImportSummary($importType, $publicId);
        } else {
            $this->importSummaryModel = $this->createNewReport($group, $importType);
        }

        if (empty($this->importSummaryModel)) {
            throw new \LogicException(
                sprintf(
                    'Can\'t find/create import summary report by public ID "%s", group ID "%s" and importType "%s"',
                    $publicId,
                    $group->getId(),
                    $importType
                )
            );
        }

        $this->logger->debug(sprintf('Get summary report from DB, ID(%s)', $this->importSummaryModel->getId()));

        $this->logger->debug('Finish initialize import summary report');
    }

    /**
     * @return int
     */
    public function getReportPublicId()
    {
        return $this->importSummaryModel->getPublicId();
    }

    /**
     * Total rows in the csv file
     *
     * @param integer $total
     */
    public function setTotal($total)
    {
        if ($this->importSummaryModel->getCountTotal() > 0) {
            return;
        }

        $this->importSummaryModel->setCountTotal($total);
    }

    /**
     * Increment the number of new contracts created.
     */
    public function incrementNewContract()
    {
        $this->logger->debug('Import summary report: increment new');
        $countNew = $this->importSummaryModel->getCountNew();
        $countNew++;
        $this->importSummaryModel->setCountNew($countNew);
    }

    /**
     * Increment the number of invite emails sent.
     */
    public function incrementInvited()
    {
        $this->logger->debug('Import summary report: increment invited');
        $countInvited = $this->importSummaryModel->getCountInvited();
        $countInvited++;
        $this->importSummaryModel->setCountInvited($countInvited);
    }

    /**
     * Increment the number of skipped row
     *
     * @param integer $offset
     */
    public function incrementSkipped(array $rowContent)
    {
        $importError = new ImportError();
        $importError->setRowContent($rowContent);
        $importError->setImportSummary($this->importSummaryModel);

        if ($this->isExistEntityErrorInTheDB($importError)) {
            return;
        }
        $this->logger->debug('Import summary report: increment skipped');
        $countSkipped = $this->importSummaryModel->getCountSkipped();
        $countSkipped++;
        $this->importSummaryModel->setCountSkipped($countSkipped);
    }

    /**
     * Increment the contract matched (or updated) counter.
     */
    public function incrementMatched()
    {
        $this->logger->debug('Import summary report: increment matched');
        $countMatched = $this->importSummaryModel->getCountMatched();
        $countMatched++;
        $this->importSummaryModel->setCountMatched($countMatched);
    }

    /**
     * Create an error record for this row with details of the error
     *
     * @param array   $row
     * @param array   $errorMessages
     */
    public function addError(array $row, array $errorMessages)
    {
        $importError = new ImportError();
        $importError->setMessages($errorMessages);
        $importError->setRowContent($row);
        $importError->setImportSummary($this->importSummaryModel);

        $this->saveImportError($importError);
    }

    /**
     * Create an exception error record for this row.
     *
     * @param array   $row
     * @param string  $exceptionMessage
     * @param string  $exceptionId
     * @param integer $offset
     */
    public function addException(array $row, $exceptionMessage, $exceptionId)
    {
        $importError = new ImportError();
        $importError->setMessages([$exceptionMessage]);
        $importError->setRowContent($row);
        $importError->setExceptionUid($exceptionId);
        $importError->setImportSummary($this->importSummaryModel);

        $this->saveImportError($importError);
    }

    /**
     * Save changes to DB
     */
    public function save()
    {
        if ($this->importSummaryModel instanceof ImportSummary) {
            $this->em->persist($this->importSummaryModel);
            $this->em->flush($this->importSummaryModel);
        }
    }

    /**
     * @param ImportError $importError
     */
    protected function saveImportError(ImportError $importError)
    {
        if ($this->isExistEntityErrorInTheDB($importError)) {
            return;
        }
        $this->logger->debug(
            sprintf('Add error to import summary report, row: "%s"', $importError->getStringRow())
        );
        $this->em->persist($importError);
        $this->em->flush($importError);
    }

    /**
     * @param  ImportError $importError
     * @return boolean
     */
    protected function isExistEntityErrorInTheDB(ImportError $importError)
    {
        $searchParameters = [
            'md5RowContent' => $importError->getMd5RowContent(),
            'importSummary' => $importError->getImportSummary()->getId()
        ];

        if ($importError->getExceptionUid()) {
            $searchParameters['exceptionUid'] = $importError->getExceptionUid();
        }

        $importError = $this->em->getRepository('RjDataBundle:ImportError')->findOneBy($searchParameters);

        return $importError instanceof ImportError;
    }

    /**
     * @param Group  $group
     * @param string $importType
     *
     * @return ImportSummary
     */
    protected function createNewReport(Group $group, $importType)
    {
        $importSummaryModel = new ImportSummary();
        $importSummaryModel->setGroup($group);
        $importSummaryModel->setType($importType);

        $this->em->persist($importSummaryModel);
        $this->em->flush($importSummaryModel);

        $this->logger->debug(sprintf('New summary report created, ID(%s)', $importSummaryModel->getId()));

        return $importSummaryModel;
    }

    /**
     * @param Group   $group
     * @param string  $importType
     * @param integer $publicId
     *
     * @return ImportSummary
     */
    protected function findImportSummary($importType, $publicId)
    {
        if (!ImportType::isValid($importType)) {
            ImportType::throwsInvalid($importType);
        }

        return $this->em->getRepository('RjDataBundle:ImportSummary')->findOneBy(
            [
                'type'  => $importType,
                'publicId' => $publicId
            ]
        );
    }
}
