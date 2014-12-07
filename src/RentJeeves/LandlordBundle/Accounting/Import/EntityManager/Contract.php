<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\LandlordBundle\Model\Import;

trait Contract
{
    protected function setYardiPaymentAccepted(EntityContract $contract, $row)
    {
        if (isset($row[Mapping::KEY_PAYMENT_ACCEPTED])) {
            $contract->setYardiPaymentAccepted($row[Mapping::KEY_PAYMENT_ACCEPTED]);
        }
    }

    /**
     * @param $row
     * @param $tenant
     *
     * @return EntityContract
     */
    protected function createContract(array $row, Tenant $tenant, Import $import)
    {
        $contract = new EntityContract();
        if ($tenant->getId()) {
            $contract->setStatus(ContractStatus::APPROVED);
        } else {
            $contract->setStatus(ContractStatus::INVITE);
        }

        $property = $this->getProperty($row);
        if ($property) {
            $contract->setProperty($property);
        }
        $contract->setGroup($this->group);
        $contract->setHolding($this->group->getHolding());
        $contract->setTenant($tenant);

        if ($unit = $this->getUnit($row, $contract->getProperty())) {
            $contract->setUnit($unit);
        }
        $contract->setDueDate($this->group->getGroupSettings()->getDueDate());
        $moveIn = $this->getDateByField($import, $row[Mapping::KEY_MOVE_IN]);
        $contract->setStartAt($moveIn);

        /**
         * If we don't have unit and property don't have flag is_single set it to single by default
         */
        if (empty($row[Mapping::KEY_UNIT]) && $property && is_null($property->getIsSingle())) {
            $property->setIsSingle(true);
        }

        $tenant->addContract($contract);

        return $contract;
    }

    /**
     * @param EntityContract $contract
     * @param $dueDate
     * @param $isNeedCreateCashOperation
     */
    protected function movePaidToOfContract(EntityContract $contract, $dueDate)
    {
        if ($this->isNeedCreateCashOperation($contract)) {
            $paidTo = new DateTime();
            $paidTo->modify('+1 month');
            $paidTo->setDate(
                $paidTo->format('Y'),
                $paidTo->format('n'),
                $dueDate
            );

            $contract->setPaidTo($paidTo);
        }
    }

    /**
     * @param EntityContract $contract
     * @return bool
     */
    protected function isNeedCreateCashOperation(EntityContract $contract)
    {
        $isNeedCreateCashOperation = false;
        $paidTo = new DateTime();
        $balance = $contract->getIntegratedBalance();
        $currentPaidTo = $contract->getPaidTo();
        if ($contract->getId() !== null) {
            // normally, we don't want to mess with paid_to for existing contracts unless
            // it is obvious someone paid outside of RentTrack:
            if ($balance <= 0 && $currentPaidTo <= $paidTo) {
                // will be in future
                //if (there is no order with paid_for for this month) {
                // create new cash payment on $groupDueDate for this month
                //}
                $isNeedCreateCashOperation = true;
            }
        } else {
            // this contract is new, so let's set paid_to accordingly
            // Set paidTo to next month if balance is <=0 so that the next month shows up in PaidFor in the wizard
            if ($balance <= 0) {
                $isNeedCreateCashOperation = true;
            }
        }

        return $isNeedCreateCashOperation;
    }

    /**
     * @param Import $import
     * @param array $row
     * @param $dueDate
     */
    protected function attachOperationToImport(ModelImport $import, $dueDate)
    {
        $contract = $import->getContract();
        if ($contract->getStatus() === ContractStatus::CURRENT &&
            !$import->isIsHasPaymentMapping()
        ) {
            $paidFor = new DateTime();
            $paidFor->setDate(
                $paidFor->format('Y'),
                $paidFor->format('n'),
                $dueDate
            );

            $import->setOperation($operation = $this->getOperationByContract($contract, $import, $paidFor));

            return $operation;
        }

        return null;
    }

    protected function getDueDateOfContract(EntityContract $contract)
    {
        $groupSettings = $this->group->getGroupSettings();
        $dueDate = ($contract->getDueDate())? $contract->getDueDate() : $groupSettings->getDueDate();

        return $dueDate;
    }

    /**
     * @param Import $import
     * @param $row
     *
     * @return Contract
     */
    protected function getContract(ModelImport $import, array $row)
    {
        $tenant = $import->getTenant();
        $property = $this->getProperty($row);

        if (!$tenant->getId() || !$property) {
            $contract = $this->createContract($row, $tenant, $import);
        } else {
            $contract = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                ($property->isSingle()) ? Unit::SINGLE_PROPERTY_UNIT_NAME : $row[Mapping::KEY_UNIT],
                isset($row[Mapping::KEY_UNIT_ID])? $row[Mapping::KEY_UNIT_ID] : null
            );

            if (empty($contract)) {
                $contract = $this->createContract($row, $tenant, $import);
            }
        }
        $import->setContract($contract);
        $this->setYardiPaymentAccepted($contract, $row);
        //set data from csv file
        $contract->setIntegratedBalance($row[Mapping::KEY_BALANCE]);
        $contract->setRent($row[Mapping::KEY_RENT]);
        $dueDate = $this->getDueDateOfContract($contract);

        $isNeedCreateCashOperation = $this->isNeedCreateCashOperation($contract);

        if ($isNeedCreateCashOperation) {
            $this->attachOperationToImport($import, $dueDate);
        }

        if (!empty($row[Mapping::KEY_MOVE_OUT])) {
            $import->setMoveOut($this->getDateByField($import, $row[Mapping::KEY_MOVE_OUT]));
        }

        $today = new DateTime();
        $leaseEnd = $this->getDateByField($import, $row[Mapping::KEY_LEASE_END]);

        if ($import->getMoveOut() !== null) {
            $contract->setFinishAt($import->getMoveOut());
            if ($import->getMoveOut() <= $today) { // only finish the contract if MoveOut is today or earlier
                $this->isFinishedContract($contract);
            }
        } elseif (isset($row[Mapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[Mapping::KEY_MONTH_TO_MONTH] == 'Y')
        ) {
            $contract->setFinishAt(null);
        } elseif (isset($row[Mapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[Mapping::KEY_MONTH_TO_MONTH]) == 'N' &&
            $leaseEnd <= $today
        ) {
            $contract->setFinishAt($leaseEnd);
            $this->isFinishedContract($contract);
        } else {
            $contract->setFinishAt($leaseEnd);
        }

        return $contract;
    }

    protected function getContractWaiting(
        Tenant $tenant,
        EntityContract $contract,
        ResidentMapping $residentMapping
    ) {
        $contractWaiting = $this->mapping->createContractWaiting(
            $tenant,
            $contract,
            $residentMapping
        );

        $contractWaiting->setYardiPaymentAccepted($contract->getYardiPaymentAccepted());

        if (!$contractWaiting->getProperty()) {
            return $contractWaiting;
        }
        /**
         * @var $contractWaitingInDb ContractWaiting
         */
        $contractWaitingInDb = $this->em->getRepository('RjDataBundle:ContractWaiting')->findOneBy(
            $contractWaiting->getImportDataForFind()
        );

        if ($contractWaitingInDb) {
            //Do update some fields
            $contractWaitingInDb->setRent($contractWaiting->getRent());
            $contractWaitingInDb->setIntegratedBalance($contractWaiting->getIntegratedBalance());
            $contractWaitingInDb->setStartAt($contractWaiting->getStartAt());
            $contractWaitingInDb->setFinishAt($contractWaiting->getFinishAt());
            $contractWaitingInDb->setYardiPaymentAccepted($contractWaiting->getYardiPaymentAccepted());
            return $contractWaitingInDb;
        }

        return $contractWaiting;
    }

    /**
     * Modify contract status if needed
     *
     * @param EntityContract $contract
     * @return bool
     */
    protected function isFinishedContract(EntityContract $contract)
    {
        if ($this->contractInPast($contract)) {
            $contract->setStatus(ContractStatus::FINISHED);

            return true;
        }

        return false;
    }

    protected function contractInPast(EntityContract $contract)
    {
        $today = new DateTime();
        return ($contract->getFinishAt() && $contract->getFinishAt() < $today)? true : false;
    }
}
