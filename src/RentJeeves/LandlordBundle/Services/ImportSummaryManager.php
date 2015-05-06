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
use Monolog\Logger;
use RentJeeves\ExternalApiBundle\Traits\DebuggableTrait;

/**
 * @Service("import_summary.manager")
 */
class ImportSummaryManager
{
    use DebuggableTrait;

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
     *     "em" = @Inject("logger")
     * })
     */
    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param Group   $group
     * @param string  $importType
     * @param integer $publicId
     */
    public function initialize(Group $group, $importType, $publicId = null)
    {
        $this->debugMessage('Initialize import summary report');
        $this->checkImportType($importType);

        if (!empty($publicId)) {
            $this->importSummaryModel = $this->findImportSummary($group, $importType, $publicId);
        } else {
            $this->importSummaryModel = $this->createNewReport($group, $importType);
        }

        $this->debugMessage('Finish initialize import summary report');
    }

    /**
     * Total rows in the csv file
     *
     * @param $total
     */
    public function setTotal($total)
    {
        $this->importSummaryModel->setCountTotal($total);
    }

    /**
     * Increment the number of new contracts created.
     */
    public function incrementNew()
    {
        $this->debugMessage('Import summary report: increment new');
        $countNew = $this->importSummaryModel->getCountNew()+1;
        $this->importSummaryModel->setCountNew($countNew);
    }

    /**
     * Increment the number of invite emails sent.
     */
    public function incrementInvited()
    {
        $this->debugMessage('Import summary report: increment invited');
        $countInvited = $this->importSummaryModel->getCountInvited()+1;
        $this->importSummaryModel->setCountInvited($countInvited);
    }

    /**
     * Increment the contract matched (or updated) counter.
     */
    public function incrementMatched()
    {
        $this->debugMessage('Import summary report: increment matched');
        $countMatched = $this->importSummaryModel->getCountMatched()+1;
        $this->importSummaryModel->setCountMatched($countMatched);
    }

    /**
     * Create an error record for this row with details of the error
     *
     * @param array   $row
     * @param array   $errorMessages
     * @param integer $offset
     */
    public function addError(array $row, array $errorMessages, $offset)
    {
        $importError = new ImportError();
        $importError->setMessages($errorMessages);
        $importError->setRowOffset($offset);
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
    public function addException(array $row, $exceptionMessage, $exceptionId, $offset)
    {
        $importError = new ImportError();
        $importError->setMessages([$exceptionMessage]);
        $importError->setRowOffset($offset);
        $importError->setRowContent($row);
        $importError->setExceptionUid($exceptionId);
        $importError->setImportSummary($this->importSummaryModel);

        $this->saveImportError($importError);
    }

    /**
     * @param ImportError $importError
     */
    protected function saveImportError(ImportError $importError)
    {
        if ($this->isExistEntityErrorInTheDB($importError)) {
            return;
        }
        $this->debugMessage(
            sprintf('Add error to import summary report, row offset "%s"', $importError->getRowOffset())
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
            'rowOffser' => $importError->getRowOffset(),
            'importSummart' => $importError->getImportSummary()->getId()
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

        $this->debugMessage(sprintf('Create summary report, ID(%s)', $importSummaryModel->getId()));

        return $importSummaryModel;
    }

    /**
     * @param $importType
     */
    protected function checkImportType($importType)
    {
        if (!in_array($importType, $importTypes = ImportType::values())) {
            throw new \LogicException(
                sprintf(
                    'Import type must be one of "%s", but given "%s"',
                    implode(',', $importTypes),
                    $importType
                )
            );
        }
    }

    /**
     * @param Group   $group
     * @param string  $importType
     * @param integer $publicId
     *
     * @return ImportSummary
     */
    protected function findImportSummary(Group $group, $importType, $publicId)
    {
        $this->checkImportType($importType);

        $importSummaryModel = $this->em->getRepository('RjDataBundle:ImportSummary')->findOneBy(
            [
                'group' => $group->getId(),
                'type'  => $importType,
                'publicId' => $publicId
            ]
        );

        if (empty($importSummaryModel)) {
            throw new \LogicException(
                sprintf(
                    'Can\'t find import summary report by public ID "%s"',
                    $publicId
                )
            );
        }

        $this->debugMessage(sprintf('Get summary report from DB, ID(%s)', $importSummaryModel->getId()));

        return $importSummaryModel;
    }
}
