<?php
namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;

/**
 * @DI\Service("checkout.paid_for")
 */
class PaidFor
{
    /**
     * @param Contract $contract
     *
     * @return array
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
        $paidForDate = $contract->getOperations()->first()->getPaidFor();
        /** @var $operation Operation */
        foreach ($contract->getOperations() as $operation) {
            $stringDate = $operation->getPaidFor()->format('Y-m-d');
            if (isset($return[$stringDate])) {
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
        return $return;
    }

    protected function getNow()
    {
        return new DateTime();
    }

    public function createItem(\DateTime $date)
    {
        return array($date->format('Y-m-d') => $date->format('M'));
    }

    protected function makeDatesFromDate(\DateTime $start)
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
