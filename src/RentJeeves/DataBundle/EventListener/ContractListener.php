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
use RentJeeves\DataBundle\Enum\PaymentStatus;

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

    /**
     * Checks contract to contain unit
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Contract) {
            return;
        }
        $this->checkContract($entity);
    }

    /**
     * @param Contract $contract
     * @param PreUpdateEventArgs $eventArgs
     */
    public function monitoringContractAmount(Contract $contract, PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('rent')) {
            if (($payment = $contract->getActivePayment()) && $payment->getAmount() != $contract->getRent()) {

                $this->hasToClosePayment = true;

                $this->container->get('project.mailer')->sendContractAmountChanged($contract, $payment);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Contract) {
            return;
        }
        $this->monitoringContractAmount($entity, $eventArgs);
        $this->checkContract($entity);
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
                $payment->setStatus(PaymentStatus::CLOSE);
                $eventArgs->getEntityManager()->persist($payment);
                // FIXME http://www.doctrine-project.org/jira/browse/DDC-2726
//                $eventArgs->getEntityManager()->flush($payment);
            }
        }
    }
}
