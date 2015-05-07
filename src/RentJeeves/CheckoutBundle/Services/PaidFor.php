<?php
namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @DI\Service("checkout.paid_for")
 */
class PaidFor
{
    /**
     * Number of return results
     * @var int
     */
    const RESULTS_COUNT = 6;

    /**
     * @var string
     */
    protected $dueDate;

    /**
     * Return array of months to pay based on contract
     *
     * @param Contract $contract
     *
     * @return array
     */
    public function getBaseArray(Contract $contract)
    {
        $return = [];
        $this->setDueDate($contract);

        if ($contract->getStatus() == ContractStatus::INVITE || $contract->getStatus() == ContractStatus::APPROVED) {
            return $this->returnDefaultValue($return);
        }

        if ($paidTo = $contract->getPaidToWithDueDate()) {
            $return = $this->makeDatesFromDate($paidTo);
        }
        if (!$contract->getOperations()->count()) {
            return $this->returnDefaultValue($return);
        }
        $balances = array();
        $paidForDate = $contract->getOperations()->first()->getPaidFor();

        /** @var $operation Operation */
        foreach ($contract->getOperations() as $operation) {
            $stringDate = $operation->getPaidFor()->format('Y-m-d');

            if (empty($balances[$stringDate])) {
                $balances[$stringDate] = 0;
            }

            $balances[$stringDate] += $operation->getAmount();
            if (isset($return[$stringDate]) &&
                $balances[$stringDate] >= $contract->getRent() &&
                OrderStatus::COMPLETE == $operation->getOrder()->getStatus()
            ) {
                unset($return[$stringDate]);
            }

            $diff = $paidForDate->diff($operation->getPaidFor());
            if (45 < $diff->days) {
                $mustBePaid = clone $paidForDate;
                $return += $this->createItem($mustBePaid->modify('+1 month'));
            }
            if ($operation->getPaidFor() != $paidForDate) {
                $paidForDate = $operation->getPaidFor();
            }
        }
        ksort($return);

        if (static::RESULTS_COUNT < ($count = count($return))) {
            $return = array_slice($return, (static::RESULTS_COUNT * -1), static::RESULTS_COUNT, true);
        }

        return $this->returnDefaultValue($return);
    }

    /**
     * Return array of months(add current and next) to pay based on contract
     *
     * @param Contract $contract
     *
     * @return array Payed for dates
     *               key string Date in format 'Y-m-d'
     *               val string Month in format 'M'
     */
    public function getArray(Contract $contract)
    {
        $this->setDueDate($contract);
        if ($contract->getStatus() == ContractStatus::INVITE || $contract->getStatus() == ContractStatus::APPROVED) {
            return $this->returnDefaultValue();
        }

        $return = $this->getBaseArray($contract);

        $date = clone $this->getNow();
        $date->setDate(null, null, $this->dueDate);
        if (!array_search($date->format('M'), $return)) {
            $return += $this->createItem($date);
        }
        if (!array_search($date->modify('+1 month')->format('M'), $return)) {
            $return += $this->createItem($date);
        }
        ksort($return);

        return $return;
    }

    protected function getNow()
    {
        return new DateTime();
    }

    public function createItem(DateTime $date)
    {
        return array($date->format('Y-m-d') => $date->format('M'));
    }

    protected function makeDatesFromDate(DateTime $start)
    {
        $now = clone $this->getNow();
        $now->modify('+1 month');

        $date = clone $start;
        $return = array();
        do {
            $return += $this->createItem($date);
        } while ($date->modify('+1 month') < $now);

        return $return;
    }

    protected function returnDefaultValue(array $value = [])
    {
        if (!count($value)) {
            $date = clone $this->getNow();
            $date->setDate(null, null, $this->dueDate);

            return $this->createItem($date) + $this->createItem($date->modify('+1 month'));
        }

        return $value;
    }

    protected function setDueDate(Contract $contract)
    {

        if ($contract->getDueDate()) {
            $this->dueDate = $contract->getDueDate();

            return;
        }
        if ($contract->getGroup() && $settings = $contract->getGroup()->getGroupSettings()) {
            $this->dueDate = $settings->getDueDate();

            return;
        }

        $this->dueDate = 1;
    }
}
