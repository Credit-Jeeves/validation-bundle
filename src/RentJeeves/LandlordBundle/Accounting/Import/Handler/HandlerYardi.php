<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Operation;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Property;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Resident;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Contract;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Tenant;
use RentJeeves\DataBundle\Entity\Contract as ContractEntity;
use RentJeeves\LandlordBundle\Accounting\Import\Form\FormBind;
use RentJeeves\LandlordBundle\Accounting\Import\Form\Forms;
use RentJeeves\LandlordBundle\Exception\ImportHandlerException;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.handler.yardi")
 */
class HandlerYardi extends HandlerAbstract
{
    use Forms;
    use Contract;
    use Tenant;
    use Resident;
    use Property;
    use Operation;
    use FormBind;
    use Unit;

    /**
     * @InjectParams({
     *     "translator"       = @Inject("translator"),
     *     "sessionUser"      = @Inject("core.session.landlord"),
     *     "storage"          = @Inject("accounting.import.storage.yardi"),
     *     "mapping"          = @Inject("accounting.import.mapping.yardi")
     * })
     */
    public function __construct(
        Translator $translator,
        SessionUser $sessionUser,
        StorageYardi $storage,
        MappingYardi $mapping
    ) {
        $this->user             = $sessionUser->getUser();
        $this->group            = $sessionUser->getGroup();
        $this->storage          = $storage;
        $this->mapping          = $mapping;
        $this->translator       = $translator;
    }

    public function saveInBackgroundLastMatched()
    {
        /**
         * @var $importModel Import
         */
        $importModel = $this->getLastImportModelFromFile();
        $errors = $importModel->getErrors();
        $errors = $errors[$importModel->getNumber()];
        $contract = $importModel->getContract();

        if ($importModel->getIsSkipped()) {
            $this->removeLastLineInFile();
            return;
        }

        if (empty($errors) &&
            !$importModel->getHasContractWaiting() &&
            !is_null($contract->getId()) &&
            $this->isBalanceChangeOnlyForContract($contract) &&
            !$this->isContractEndedAndActiveInOurDB($contract)
        ) {
            $this->em->flush($contract);
            $this->removeLastLineInFile();
            return;
        }

        $contractWaiting = $importModel->getContractWaiting();

        if (empty($errors) &&
            $importModel->getHasContractWaiting() &&
            !is_null($contractWaiting->getId()) &&
            $this->isBalanceChangeOnlyForContractWaiting($contractWaiting)
        ) {
            $this->em->flush($contractWaiting);
            $this->removeLastLineInFile();
            return;
        }
    }

    /**
     * @param ContractWaiting $contractWaiting
     * @return bool
     */
    protected function isBalanceChangeOnlyForContractWaiting(ContractWaiting $contractWaiting)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:ContractWaiting')->find($contractWaiting->getId());

        if ($contractInDb->getStartAt() !== $contractWaiting->getStartAt()) {
            return false;
        }

        if ($contractInDb->getFinishAt() !== $contractWaiting->getFinishAt()) {
            return false;
        }

        if ($contractInDb->getRent() !== $contractWaiting->getRent()) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     * @throws ImportHandlerException
     */
    protected function getLastImportModelFromFile()
    {
        $data       = $this->mapping->getData($this->mapping->getTotal()-2, $rowCount = 1);
        $collection = new ArrayCollection(array());

        foreach ($data as $key => $values) {
            $import = $this->getImport($values, $key);
            $import->setNumber($key);
            $collection->add($import);
        }

        if ($collection->count() !== 1) {
            throw new ImportHandlerException("Must be just one Import");
        }

        return $collection->first();
    }

    /**
     * @param ContractEntity $contract
     * @return bool
     */
    protected function isBalanceChangeOnlyForContract(ContractEntity $contract)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:Contract')->find($contract->getId());

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

    protected function isContractEndedAndActiveInOurDB(ContractEntity $contract)
    {
        $contractInDb = $this->em->getRepository('RjDataBundle:Contract')->find($contract->getId());

        if (in_array(
            $contractInDb->getStatus(),
            array(ContractStatus::CURRENT, ContractStatus::INVITE, ContractStatus::APPROVED)
        ) &&
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
}
