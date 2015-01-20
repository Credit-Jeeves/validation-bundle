<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\YardiPaymentAccepted;
use Exception;

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
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 */
class ContractListener
{
    /**
     * @DI\Inject("service_container", required = true)
     */
    public $container;

    public $hasToClosePayment = false;

    public function checkContract(Contract $contract)
    {
        // don't check finished and deleted contracts
        if (in_array($contract->getStatus(), array(ContractStatus::DELETED, ContractStatus::FINISHED))) {
            return;
        }

        // if property is standalone we just add system unit to the contract
        $property = $contract->getProperty();
        if ($property->isSingle() && $unit = $property->getSingleUnit()) {
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

    protected function turnOnTUReporting(Contract $contract)
    {
        $tuReporting = $this->container->get('contract.trans_union_reporting');
        $tuReporting->turnOnTransUnionReporting($contract);
    }

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
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    public function monitoringContractAmount(Contract $contract, PreUpdateEventArgs $eventArgs)
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

        if (!($payment = $contract->getActivePayment())) {
            return;
        }

        if ($payment->getAmount() === $contract->getRent()) {
            return;
        }

        $this->hasToClosePayment = true;
        $this->container->get('project.mailer')->sendContractAmountChanged($contract, $payment);
    }

    public function updateBalanceForCurrentStatus(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if (!$eventArgs->hasChangedField('status')) {
            return;
        }

        $newValue = $eventArgs->getNewValue('status');

        if ($newValue !== ContractStatus::CURRENT) {
            return;
        }

        $contract->setBalance($contract->getRent());
    }

    protected function isPaymentAcceptedFieldChanged(PreUpdateEventArgs $eventArgs)
    {
        if (!$eventArgs->hasChangedField('yardiPaymentAccepted')) {
            return false;
        }

        $newValue = (int) $eventArgs->getNewValue('yardiPaymentAccepted');
        $oldValue = (int) $eventArgs->getOldValue('yardiPaymentAccepted');

        if ($oldValue === $newValue) {
            return false;
        }
        $deniedPaymentStatuses = array(
            YardiPaymentAccepted::DO_NOT_ACCEPT,
            YardiPaymentAccepted::CASH_EQUIVALENT
        );

        if (in_array($newValue, $deniedPaymentStatuses) && in_array($oldValue, $deniedPaymentStatuses)) {
            return false;
        }

        return true;
    }

    protected function closePaymentByYardi(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if ($this->isPaymentAcceptedFieldChanged($eventArgs) === false) {
            return;
        }

        $payment = $contract->getActivePayment();
        if (empty($payment)) {
            return;
        }

        $payment->setClosed($this, PaymentCloseReason::CONTRACT_CHANGED);
        $eventArgs->getEntityManager()->flush($payment);
    }

    protected function sendYardiPaymentEmail(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if ($this->isPaymentAcceptedFieldChanged($eventArgs) === false) {
            return;
        }

        $newValue = (int) $eventArgs->getNewValue('yardiPaymentAccepted');
        $result = true;

        switch ($newValue) {
            case YardiPaymentAccepted::ANY:
                $result = $this->container->get('project.mailer')
                    ->sendEmailAcceptYardiPayment($contract->getTenant());
                break;
            case YardiPaymentAccepted::DO_NOT_ACCEPT:
            case YardiPaymentAccepted::CASH_EQUIVALENT:
                $result = $this->container->get('project.mailer')
                    ->sendEmailDoNotAcceptYardiPayment($contract->getTenant());
                break;
        }

        if ($result !== true) {
            throw new Exception(
                sprintf(
                    "Email(payment yardi permission) don't send for user: %s",
                    $contract->getTenant()->getEmail()
                )
            );
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $contract = $eventArgs->getEntity();
        if (!$contract instanceof Contract) {
            return;
        }
        $this->monitoringContractAmount($contract, $eventArgs);
        $this->checkContract($contract);
        $this->updateBalanceForCurrentStatus($contract, $eventArgs);
        $this->closePaymentByYardi($contract, $eventArgs);
        $this->sendYardiPaymentEmail($contract, $eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var Contract $entity */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Contract) {
            return;
        }
        if ($this->hasToClosePayment) {
            $this->hasToClosePayment = false;
            if ($payment = $entity->getActivePayment()) {
                $payment->setClosed($this, PaymentCloseReason::CONTRACT_CHANGED);
                $eventArgs->getEntityManager()->persist($payment);
                $eventArgs->getEntityManager()->flush($payment);
            }
        }
    }
}
