<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @DI\Service("data.event_listener.contract")
 * @DI\Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 * @DI\Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="preUpdate",
 *         "method"="preUpdate"
 *     }
 * )
 * @DI\Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postPersist",
 *         "method"="postPersist"
 *     }
 * )
 */
class ContractListener
{
    /**
     * I can't just inject service which I need because have error
     *   [Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException]
     *
     * @DI\Inject("service_container", required = true)
     */
    public $container;

    /**
     * Checks contract to contain unit
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $contract = $eventArgs->getEntity();
        if (!$contract instanceof Contract) {
            return;
        }
        $this->checkContract($contract);
        $this->turnOnTUReporting($contract);
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $contract = $eventArgs->getEntity();
        if (!$contract instanceof Contract) {
            return;
        }
        $this->monitoringContractAmount($contract, $eventArgs);
        $this->checkContract($contract);
        $this->closePaymentByAccounting($contract, $eventArgs);
        $this->sendOnlinePaymentEmail($contract, $eventArgs);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $contract = $event->getEntity();
        if (!$contract instanceof Contract) {
            return;
        }

        $this->registerToProfitStars($contract);
    }

    /**
     * prePersist
     * Check that we can create contract and set default values
     * @param Contract $contract
     */
    protected function checkContract(Contract $contract)
    {
        // don't check finished and deleted contracts
        if (in_array($contract->getStatus(), [ContractStatus::DELETED, ContractStatus::FINISHED])) {
            return;
        }

        // if property is standalone we just add system unit to the contract
        $property = $contract->getProperty();
        $propertyAddress = $property->getPropertyAddress();
        if ($propertyAddress->isSingle() && $unit = $property->getExistingSingleUnit()) {
            $contract->setUnit($unit);

            return;
        }

        // contract should have unit and that unit should belong to the contract property
        $unit = $contract->getUnit();
        if ($unit instanceof Unit && $unit->getProperty()->getId() == $property->getId()) {
            return;
        }

        // contract can be without unit ONLY if it is in a PENDING status and 'search' is not null
        if (!$unit && $contract->getStatus() == ContractStatus::PENDING && $contract->getSearch()) {
            return;
        }

        throw new LogicException('Invalid contract parameters');
    }

    /**
     * prePersist
     * @param Contract $contract
     */
    protected function turnOnTUReporting(Contract $contract)
    {
        $this->container->get('contract.trans_union_reporting')->turnOnTransUnionReporting($contract);
    }

    /**
     * preUpdate
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    protected function monitoringContractAmount(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if (!$eventArgs->hasChangedField('rent')) {
            return;
        }
        /**
         * 500 - string
         * 500.00 - double
         * value the same but contract will close.
         */
        $newValue = floatval($eventArgs->getNewValue('rent'));
        $oldValue = floatval($eventArgs->getOldValue('rent'));

        if ($oldValue === $newValue) {
            return;
        }

        if (!($payment = $contract->getActiveRentPayment())) {
            return;
        }

        $paymentAmount = floatval($payment->getAmount());
        if ($paymentAmount === $newValue) {
            return;
        }

        $paymentTotal = floatval($payment->getTotal());
        if ($paymentTotal === $newValue) {
            return;
        }

        if ($this->isInOlenAmountChangedEmailBlacklist($contract)) {
            return;
        }

        $this->container->get('project.mailer')->sendContractAmountChanged($contract, $payment);
    }

    /**
     * preUpdate
     * @param PreUpdateEventArgs $eventArgs
     * @return bool
     */
    protected function isPaymentAcceptedFieldChanged(PreUpdateEventArgs $eventArgs)
    {
        if (!$eventArgs->hasChangedField('paymentAccepted')) {
            return false;
        }

        $newValue = (int) $eventArgs->getNewValue('paymentAccepted');
        $oldValue = (int) $eventArgs->getOldValue('paymentAccepted');

        if ($oldValue === $newValue) {
            return false;
        }
        $deniedPaymentStatuses = [
            PaymentAccepted::DO_NOT_ACCEPT,
            PaymentAccepted::CASH_EQUIVALENT
        ];

        if (in_array($newValue, $deniedPaymentStatuses) && in_array($oldValue, $deniedPaymentStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     * @return boolean
     */
    protected function isOnlinePaymentAccessChanged(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        $isPaymentAcceptedChanged = $this->isPaymentAcceptedFieldChanged($eventArgs);
        $isPaymentAllowedChanged = false;

        if ($eventArgs->hasChangedField('paymentAllowed') &&
            (bool) $eventArgs->getNewValue('paymentAllowed') !== (bool) $eventArgs->getOldValue('paymentAllowed')
        ) {
            $isPaymentAllowedChanged = true;
        }

        if (!$isPaymentAcceptedChanged && !$isPaymentAllowedChanged) {
            return false;
        }

        if ($isPaymentAcceptedChanged) {
            $paymentAcceptedNew = (int) $eventArgs->getNewValue('paymentAccepted');
            $paymentAcceptedOld = (int) $eventArgs->getOldValue('paymentAccepted');
        } else {
            $paymentAcceptedNew = $paymentAcceptedOld = (int) $contract->getPaymentAccepted();
        }

        if ($isPaymentAllowedChanged) {
            $paymentAllowedNew = (bool) $eventArgs->getNewValue('paymentAllowed');
            $paymentAllowedOld = (bool) $eventArgs->getOldValue('paymentAllowed');
        } else {
            $paymentAllowedNew = $paymentAllowedOld = (bool) $contract->isPaymentAllowed();
        }

        return ($paymentAcceptedOld === PaymentAccepted::ANY && $paymentAllowedOld) !==
        ($paymentAcceptedNew === PaymentAccepted::ANY && $paymentAllowedNew);
    }

    /**
     * preUpdate
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    protected function closePaymentByAccounting(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if ($this->isPaymentAcceptedFieldChanged($eventArgs) === false) {
            return;
        }

        $payment = $contract->getActiveRentPayment();
        if (empty($payment)) {
            return;
        }

        $oldValue = $payment->getCloseDetails();
        $payment->setClosed($this, PaymentCloseReason::CONTRACT_CHANGED);
        $newValue = $payment->getCloseDetails();
        $eventArgs->getEntityManager()->getUnitOfWork()->scheduleExtraUpdate(
            $payment,
            ['closeDetails' => [$oldValue, $newValue]]
        );
    }

    /**
     * preUpdate
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    protected function sendOnlinePaymentEmail(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if (!$this->isOnlinePaymentAccessChanged($contract, $eventArgs)) {
            return;
        }

        $accountingAccepted = $eventArgs->hasChangedField('paymentAccepted') ?
            (int) $eventArgs->getNewValue('paymentAccepted') :
            (int) $contract->getPaymentAccepted();
        $paymentAllowed = $eventArgs->hasChangedField('paymentAllowed') ?
            $eventArgs->getNewValue('paymentAllowed') :
            $contract->isPaymentAllowed();

        if ($accountingAccepted === PaymentAccepted::ANY && $paymentAllowed) {
            $result = $this->container->get('project.mailer')
                ->sendEmailAcceptPayment($contract->getTenant());
        } else {
            $result = $this->container->get('project.mailer')
                ->sendEmailDoNotAcceptPayment($contract->getTenant());
        }

        if (!$result) {
            $this->container->get('logger')->alert(
                sprintf(
                    'ContractListener failed to send the "Payment %sAccepted" email to user:%s for contract #%d',
                    (PaymentAccepted::ANY === $accountingAccepted  && $paymentAllowed) ? '' : 'Not ',
                    $contract->getTenant()->getEmail(),
                    $contract->getId()
                )
            );
        }
    }

    /**
     * @param Contract $contract
     */
    protected function registerToProfitStars(Contract $contract)
    {
        $profitStarsLocations = $contract->getGroup()->getDepositAccounts();
        /** @var DepositAccount $depositAccount */
        foreach ($profitStarsLocations as $depositAccount) {
            if (PaymentProcessor::PROFIT_STARS === $depositAccount->getPaymentProcessor() &&
                DepositAccountStatus::DA_COMPLETE === $depositAccount->getStatus()
            ) {
                $job = new Job(
                    'renttrack:payment-processor:profit-stars:register-contract',
                    [$contract->getId(), $depositAccount->getId()]
                );

                $this->getEntityManager()->persist($job);
                $this->getEntityManager()->flush($job);
            }
        }
    }

    /**
     *
     * Olen does not want us to send rent changed emails for these groups until
     * they have converted their recurring charges providers (RT-2189)
     *
     * @param Contract $contract
     * @return bool
     */
    protected function isInOlenAmountChangedEmailBlacklist(Contract $contract)
    {
        $groupId = $contract->getGroupId();
        $olenGroupEmailBlacklist = [
            1608 => "Durango Canyon",
            1788 => "Club Lake Pointe",
            1789 => "Club Mira Lago",
            1790 => "Delray Bay",
            1791 => "Indian Hills FL",
            1792 => "Manatee Bay",
            1793 => "Players Club FL",
            1794 => "Quantum Lake Villas",
            1795 => "Sanctuary Cove",
            1796 => "Villas of Juno",
            1797 => "Weston Place",
            1798 => "Whalers Cove",
            1799 => "Seven Pines",
            1800 => "The Reserve at West Paces",
            1801 => "Arroyo Grande",
            1802 => "Breakers",
            1803 => "Canyon Villas",
            1804 => "Diamondhead",
            1805 => "Eagle Trace",
            1806 => "Falling Water",
            1807 => "Hidden Cove",
            1808 => "Horizon Ridge",
            1810 => "Indian Hills NV",
            1811 => "Invitational",
            1812 => "Morningstar",
            1813 => "Players Club NV",
            1814 => "Red Rock Villas",
            1815 => "Shelter Cove",
            1816 => "Spanish Ridge",
            1817 => "Spanish Wells",
            1818 => "Willowbrook",
            2094 => "One North Scottsdale",
            2096 => "Ibis Reserve",
        ];

        if (array_key_exists($groupId, $olenGroupEmailBlacklist)) {
            return true;
        }

        return false;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}
