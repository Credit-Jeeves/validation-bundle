<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

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
        $this->sendAccountingPaymentEmail($contract, $eventArgs);
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

        $payment->setClosed($this, PaymentCloseReason::CONTRACT_CHANGED);
        $eventArgs->getEntityManager()->flush($payment);
    }

    /**
     * preUpdate
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    protected function sendAccountingPaymentEmail(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if ($this->isPaymentAcceptedFieldChanged($eventArgs) === false) {
            return;
        }

        $newValue = (int) $eventArgs->getNewValue('paymentAccepted');
        $result = true;

        switch ($newValue) {
            case PaymentAccepted::ANY:
                $result = $this->container->get('project.mailer')
                    ->sendEmailAcceptYardiPayment($contract->getTenant());
                break;
            case PaymentAccepted::DO_NOT_ACCEPT:
            case PaymentAccepted::CASH_EQUIVALENT:
                $result = $this->container->get('project.mailer')
                    ->sendEmailDoNotAcceptYardiPayment($contract->getTenant());
                break;
        }

        if (!$result) {
            $this->container->get('logger')->alert(
                'Email(payment accounting permission) don\'t send for user: ' . $contract->getTenant()->getEmail()
            );
        }
    }
}
