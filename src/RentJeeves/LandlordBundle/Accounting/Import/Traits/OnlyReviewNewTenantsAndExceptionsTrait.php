<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\DataBundle\Entity\Contract as ContractEntity;
use RentJeeves\LandlordBundle\Model\Import;

trait OnlyReviewNewTenantsAndExceptionsTrait
{
    /**
     * @param $contract
     * @param $repository
     * @return bool
     */
    protected function isChangeImportantField($contract, $repository)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:'.$repository)->find($contract->getId());

        if ($contractInDb->getStartAt() !== $contract->getStartAt()) {
            return false;
        }

        if ($contractInDb->getFinishAt() !== $contract->getFinishAt()) {
            return false;
        }

        if ($contractInDb->getRent() !== $contract->getRent()) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     * @throws ImportHandlerException
     */
    protected function getLastModelFromFile()
    {
        $data = $this->mapping->getData($this->mapping->getTotal()-2, $rowCount = 1);

        if (count($data) > 1) {
            throw new ImportHandlerException("Must be just one csv row which mapped to array");
        }

        if (empty($data)) {
            return null;
        }

        $import = $this->getImport(end($data), 1);
        $import->setNumber(1);

        return $import;
    }

    /**
     * @param ContractEntity $contract
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

    protected function removeLastLineInFile()
    {
        // load the data and delete the line from the array
        $lines = file($this->storage->getFilePath());
        $last = sizeof($lines) - 1 ;
        unset($lines[$last]);

        // write the new data to the file
        $fp = fopen($this->storage->getFilePath(), 'w');
        fwrite($fp, implode('', $lines));
        fclose($fp);
    }

    protected function updateMatchedContractsWithCallback($callbackSuccess, $callbackFailed)
    {
        /**
         * @var $importModel Import
         */
        $importModel = $this->getLastModelFromFile();
        if (empty($importModel)) {
            return;
        }

        $errors = $importModel->getErrors();
        $errors = $errors[$importModel->getNumber()];
        $contract = $importModel->getContract();

        if ($importModel->getIsSkipped()) {
            $callbackSuccess();
            return;
        }

        if (empty($errors) &&
            !$importModel->getHasContractWaiting() &&
            !is_null($contract->getId()) &&
            $this->isChangeImportantField($contract, $repository = 'Contract')&&
            !$this->isContractEndedAndActiveInOurDB($contract)
        ) {
            $this->em->flush($contract);
            $callbackSuccess();
            return;
        }

        $contractWaiting = $importModel->getContractWaiting();

        if (empty($errors) &&
            $importModel->getHasContractWaiting() &&
            !is_null($contractWaiting->getId()) &&
            $this->isChangeImportantField($contractWaiting, $repository = 'ContractWaiting')
        ) {
            $this->em->flush($contractWaiting);
            $callbackSuccess();
            return;
        }

        $callbackFailed();
    }
}
