<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Group as EntityGroup;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Entity\Property as EntityProperty;
use RentJeeves\DataBundle\Entity\Unit as EntityUnit;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property EntityGroup group
 * @property Import currentImportModel
 * @method EntityProperty getProperty
 * @method EntityUnit getUnit
 */
trait Contract
{

    protected function setLeaseId(array $row)
    {
        if (isset($row[Mapping::KEY_EXTERNAL_LEASE_ID]) && !empty($row[Mapping::KEY_EXTERNAL_LEASE_ID])) {
            $this->currentImportModel->getContract()->setExternalLeaseId($row[Mapping::KEY_EXTERNAL_LEASE_ID]);
        }
    }

    protected function setPaymentAccepted(array $row)
    {
        if (isset($row[Mapping::KEY_PAYMENT_ACCEPTED])) {
            $this->currentImportModel->getContract()->setPaymentAccepted($row[Mapping::KEY_PAYMENT_ACCEPTED]);
        }
    }

    /**
     * @param $row
     * @param $tenan
     */
    protected function setNewContract(array $row)
    {
        $tenant = $this->currentImportModel->getTenant();
        $contract = new EntityContract();
        $this->currentImportModel->setContract($contract);

        if ($tenant->getId()) {
            $contract->setStatus(ContractStatus::APPROVED);
        } else {
            $contract->setStatus(ContractStatus::INVITE);
        }

        $property = $this->getProperty($row);
        if ($property) {
            $contract->setProperty($property);
        }
        if ($this->group && $property) {
            $property->addPropertyGroup($this->group);
            $contract->setGroup($this->group);
            $contract->setHolding($this->group->getHolding());
        }
        $contract->setTenant($tenant);

        if ($unit = $this->getUnit($row, $contract->getProperty())) {
            $contract->setUnit($unit);
        }
        if ($this->group) {
            $contract->setDueDate($this->group->getGroupSettings()->getDueDate());
        }
        $moveIn = $this->getDateByField($row[Mapping::KEY_MOVE_IN]);
        $contract->setStartAt($moveIn);

        $tenant->addContract($contract);
    }

    /**
     * @param $dueDate
     */
    public function movePaidToOfContract($dueDate)
    {
        if ($this->isNeedCreateCashOperation($this->currentImportModel->getContract())) {
            $paidTo = new DateTime();
            $paidTo->modify('+1 month');
            $paidTo->setDate(
                $paidTo->format('Y'),
                $paidTo->format('n'),
                $dueDate
            );

            $this->currentImportModel->getContract()->setPaidTo($paidTo);
        }
    }

    /**
     * @return bool
     */
    public function isNeedCreateCashOperation()
    {
        $isNeedCreateCashOperation = false;
        $paidTo = new DateTime();
        $balance = $this->currentImportModel->getContract()->getIntegratedBalance();
        $currentPaidTo = $this->currentImportModel->getContract()->getPaidTo();
        if ($this->currentImportModel->getContract()->getId() !== null) {
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
     * @param $dueDate
     */
    public function getOperationByDueDate($dueDate)
    {
        $contract = $this->currentImportModel->getContract();
        if ($contract->getStatus() === ContractStatus::CURRENT) {
            $paidFor = new DateTime();
            $paidFor->setDate(
                $paidFor->format('Y'),
                $paidFor->format('n'),
                $dueDate
            );

            $operation = $this->getOperationByPaidFor($paidFor);

            return $operation;
        }

        return null;
    }

    public function getDueDateOfContract()
    {
        $contract = $this->currentImportModel->getContract();
        if ($contract->getGroup()) {
            $groupSettings = $contract->getGroup()->getGroupSettings();
            $dueDate = ($contract->getDueDate())? $contract->getDueDate() : $groupSettings->getDueDate();

            return $dueDate;
        }

        return null;
    }

    /**
     * @param $row
     */
    protected function setContract(array $row)
    {
        $tenant = $this->currentImportModel->getTenant();
        $property = $this->getProperty($row);

        if (!$tenant->getId() || !$property) {
            $this->setNewContract($row);
        } else {
            $contract = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $tenant->getId(),
                ($property->isSingle()) ? Unit::SINGLE_PROPERTY_UNIT_NAME : $row[Mapping::KEY_UNIT],
                isset($row[Mapping::KEY_UNIT_ID])? $row[Mapping::KEY_UNIT_ID] : null,
                $property->getId()
            );
            $this->currentImportModel->setContract($contract);
        }

        if (!$this->currentImportModel->getContract()) {
            $this->setNewContract($row);
        }

        $this->setLeaseId($row);
        $this->setPaymentAccepted($row);
        //set data from csv file
        $this->currentImportModel->getContract()->setIntegratedBalance($row[Mapping::KEY_BALANCE]);
        $this->currentImportModel->getContract()->setRent($row[Mapping::KEY_RENT]);

        if (!empty($row[Mapping::KEY_MOVE_OUT])) {
            $this->currentImportModel->setMoveOut(
                $this->getDateByField(
                    $row[Mapping::KEY_MOVE_OUT]
                )
            );
        }

        $today = new DateTime();
        $leaseEnd = $this->getDateByField($row[Mapping::KEY_LEASE_END]);

        if ($this->currentImportModel->getMoveOut() !== null) {
            $this->currentImportModel->getContract()->setFinishAt($this->currentImportModel->getMoveOut());
            // only finish the contract if MoveOut is today or earlier
            if ($this->currentImportModel->getMoveOut() <= $today) {
                $this->setFinishedContract();
            }
        } elseif (isset($row[Mapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[Mapping::KEY_MONTH_TO_MONTH] == 'Y')
        ) {
            $this->currentImportModel->getContract()->setFinishAt(null);
        } elseif (isset($row[Mapping::KEY_MONTH_TO_MONTH]) &&
            strtoupper($row[Mapping::KEY_MONTH_TO_MONTH]) == 'N' &&
            $leaseEnd <= $today
        ) {
            $this->currentImportModel->getContract()->setFinishAt($leaseEnd);
            $this->setFinishedContract();
        } else {
            $this->currentImportModel->getContract()->setFinishAt($leaseEnd);
        }
    }

    protected function getContractWaiting()
    {
        $contractWaiting = $this->mapping->createContractWaiting(
            $this->currentImportModel->getTenant(),
            $this->currentImportModel->getContract(),
            $this->currentImportModel->getResidentMapping()
        );

        $contractWaiting->setPaymentAccepted(
            $this->currentImportModel->getContract()->getPaymentAccepted()
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
            $contractWaitingInDb->setPaymentAccepted($contractWaiting->getPaymentAccepted());
            return $contractWaitingInDb;
        }

        return $contractWaiting;
    }

    public function setFinishedContract()
    {
        if ($this->isContractInPast()) {
            $this->currentImportModel->getContract()->setStatus(ContractStatus::FINISHED);
        }
    }

    /**
     * @return bool
     */
    public function isContractInPast()
    {
        $today = new DateTime();
        return ($this->currentImportModel->getContract()->getFinishAt() &&
                $this->currentImportModel->getContract()->getFinishAt() < $today)? true : false;
    }
}
