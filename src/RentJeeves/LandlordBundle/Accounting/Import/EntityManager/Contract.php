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

trait Contract
{
    /**
     * @param $row
     * @param $tenant
     *
     * @return EntityContract
     */
    protected function createContract(array $row, Tenant $tenant)
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
        $moveIn = $this->getDateByField($row[Mapping::KEY_MOVE_IN]);
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
            $contract = $this->createContract($row, $tenant);
        } else {
            $contract = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                ($property->isSingle()) ? Unit::SINGLE_PROPERTY_UNIT_NAME : $row[Mapping::KEY_UNIT],
                isset($row[Mapping::KEY_UNIT_ID])? $row[Mapping::KEY_UNIT_ID] : null
            );

            if (empty($contract)) {
                $contract = $this->createContract($row, $tenant);
            }
        }
        //set data from csv file
        $contract->setIntegratedBalance($row[Mapping::KEY_BALANCE]);
        $contract->setRent($row[Mapping::KEY_RENT]);

        $paidTo = new DateTime();
        $currentPaidTo = $contract->getPaidTo();
        $groupDueDate = $this->group->getGroupSettings()->getDueDate();
        //contract is a match
        if ($contract->getId() !== null) {
            // normally, we don't want to mess with paid_to for existing contracts unless
            // it is obvious someone paid outside of RentTrack:
            if ($row[Mapping::KEY_BALANCE] <= 0 && $currentPaidTo <= $paidTo) {
                $paidTo->modify('+1 month');
                // will be in future
                //if (there is no order with paid_for for this month) {
                // create new cash payment on $groupDueDate for this month
                //}
                $paidTo->setDate(
                    $paidTo->format('Y'),
                    $paidTo->format('n'),
                    $groupDueDate
                );

                $contract->setPaidTo($paidTo);
            }
        } else {
            // this contract is new, so let's set paid_to accordingly
            // Set paidTo to next month if balance is <=0 so that the next month shows up in PaidFor in the wizard
            if ($row[Mapping::KEY_BALANCE] <= 0) {
                $paidTo->modify('+1 month');
            }

            $paidTo->setDate(
                $paidTo->format('Y'),
                $paidTo->format('n'),
                $groupDueDate
            );

            $contract->setPaidTo($paidTo);
        }

        if (!empty($row[Mapping::KEY_MOVE_OUT])) {
            $import->setMoveOut($this->getDateByField($row[Mapping::KEY_MOVE_OUT]));
        }

        $today = new DateTime();
        if ($import->getMoveOut() !== null) {
            $contract->setFinishAt($import->getMoveOut());
            if ($import->getMoveOut() <= $today) { // only finish the contract if MoveOut is today or earlier
                $this->isFinishedContract($contract);
            }
        } elseif (isset($row[Mapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[Mapping::KEY_MONTH_TO_MONTH] == 'Y')
        ) {
            $contract->setFinishAt(null);
        } else {
            $contract->setFinishAt($this->getDateByField($row[Mapping::KEY_LEASE_END]));
        }

        return $contract;
    }

    public function getContractWaiting(
        Tenant $tenant,
        EntityContract $contract,
        ResidentMapping $residentMapping
    ) {
        $contractWaiting = $this->mapping->createContractWaiting(
            $tenant,
            $contract,
            $residentMapping
        );

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
