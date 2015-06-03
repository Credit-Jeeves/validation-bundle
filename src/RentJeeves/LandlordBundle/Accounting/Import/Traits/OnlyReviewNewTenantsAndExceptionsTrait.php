<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Traits;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingInterface;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageInterface;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\DataBundle\Entity\Contract as ContractEntity;
use RentJeeves\LandlordBundle\Model\Import;
use Exception;
use RentJeeves\LandlordBundle\Services\ImportSummaryManager;

/**
 * @property EntityManager $em
 * @property MappingInterface $mapping
 * @property Import $currentImportModel
 * @property StorageInterface $storage
 * @property array $lines
 * @method manageException
 */
trait OnlyReviewNewTenantsAndExceptionsTrait
{
    /**
     * @param $contract
     * @param $repository
     * @return bool
     */
    protected function isChangedImportantField($contract, $repository)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:'.$repository)->find($contract->getId());

        if ($contractInDb instanceof ContractWaiting and $this->currentImportModel->getTenant()->getEmail()) {
            return true;
        }

        if ($contractInDb->getStartAt() !== $contract->getStartAt()) {
            return true;
        }

        if ($contractInDb->getFinishAt() !== $contract->getFinishAt()) {
            return true;
        }

        if ($contractInDb->getRent() !== $contract->getRent()) {
            return true;
        }

        return false;
    }

    /**
     * @throws ImportHandlerException
     */
    protected function createLastModelFromFile()
    {
        $data = $this->mapping->getData($this->mapping->getTotalContent()-1, $rowCount = 1);

        if (count($data) > 1) {
            throw new ImportHandlerException('Must be just one csv row which mapped to array');
        }

        if (empty($data)) {
            $this->currentImportModel = null;

            return;
        }

        $this->createCurrentImportModel(end($data), $this->mapping->getTotalContent());
    }

    /**
     * @param  ContractEntity $contract
     * @return bool
     */
    protected function isContractEndedAndActiveInOurDB(ContractEntity $contract)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:Contract')->find($contract->getId());
        $statuses =  array(ContractStatus::CURRENT, ContractStatus::INVITE, ContractStatus::APPROVED);
        if (in_array($contractInDb->getStatus(), $statuses) &&
            $contract->getStatus() === ContractStatus::FINISHED
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $filePath
     */
    protected function removeLastLineInFile($filePath)
    {
        // load the data and delete the line from the array
        $lines = file($filePath, FILE_SKIP_EMPTY_LINES);
        array_pop($lines);
        // write the new data to the file
        file_put_contents($filePath, implode('', $lines));
    }

    /**
     * @param string $filePath
     */
    protected function moveLine($filePath)
    {
        $lines = file($filePath, FILE_SKIP_EMPTY_LINES);

        if (count($lines) > 0) {
            $this->lineFromCsvFile[] = array_pop($lines);
        }

        file_put_contents($filePath, implode('', $lines));
    }

    /**
     * @param ImportSummaryManager $report
     * @param callable $callbackSuccess
     * @param callable $callbackFailed
     * @throws ImportHandlerException
     */
    protected function updateMatchedContractsWithCallback(
        ImportSummaryManager $report,
        $callbackSuccess,
        $callbackFailed
    ) {
        $this->createLastModelFromFile();
        if (!is_callable($callbackFailed) || !is_callable($callbackSuccess)) {
            return;
        }

        if (empty($this->currentImportModel)) {
            throw new \LogicException('Please check logic, current import can\'t be empty, offset');
        }

        try {
            $errors = $this->currentImportModel->getErrors()[$this->currentImportModel->getNumber()];
            $contract = $this->currentImportModel->getContract();

            if (!empty($errors)) {
                //Must Show User For Review
                call_user_func($callbackFailed);

                return;
            }

            if ($this->currentImportModel->isSkipped()) {
                $report->incrementSkipped($this->currentImportModel->getRow());
                call_user_func($callbackSuccess);

                return;
            }

            if (!$this->currentImportModel->getHasContractWaiting() &&
                !is_null($contract->getId()) &&
                !$this->isChangedImportantField($contract, $repository = 'Contract') &&
                !$this->isContractEndedAndActiveInOurDB($contract)
            ) {
                $report->incrementMatched();
                $this->em->flush($contract);
                call_user_func($callbackSuccess);

                return;
            }

            $contractWaiting = $this->currentImportModel->getContractWaiting();

            if ($this->currentImportModel->getHasContractWaiting() &&
                !is_null($contractWaiting->getId()) &&
                !$this->isChangedImportantField($contractWaiting, $repository = 'ContractWaiting')
            ) {
                $report->incrementMatched();
                $this->em->flush($contractWaiting);
                call_user_func($callbackSuccess);

                return;
            }

            call_user_func($callbackFailed);
        } catch (\Exception $e) {
            $this->manageException($e);
        }
    }
}
