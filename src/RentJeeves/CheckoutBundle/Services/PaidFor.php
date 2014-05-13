<?php
namespace RentJeeves\CheckoutBundle\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;

/**
 * @DI\Service("checkout.paid_for")
 */
class PaidFor
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getArray($contractId)
    {
        $contract = $this->em->getRepository('RjDataBundle:Contract')->getOneWithOperationsOrders($contractId);
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
