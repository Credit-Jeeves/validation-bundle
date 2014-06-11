<?php
namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;

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
     * Return array of months to pay based on contract
     *
     * @param Contract $contract
     *
     * @return array Payed for dates
     *   key string Date in format 'Y-m-d'
     *   val string Month in format 'M'
     */
    public function getArray(Contract $contract)
    {
        $return = array();
        if ($paidTo = $contract->getPaidToWithDueDate()) {
            $return = $this->makeDatesFromDate($paidTo);
        }
        if (!$contract->getOperations()->count()) {
            return $return;
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
        $now = $this->getNow()->modify('+1 month');
        $date = clone $start;
        $return = array();
        do {
            $return += $this->createItem($date);
        } while ($date->modify('+1 month') < $now);
        return $return;
    }
}
