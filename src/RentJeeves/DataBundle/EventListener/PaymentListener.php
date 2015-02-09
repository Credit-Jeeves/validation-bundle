<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\Event\OnFlushEventArgs;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @Service("data.event_listener.payment")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist" 
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="onFlush",
 *         "method"="onFlush"
 *     }
 * )
 */
class PaymentListener
{
    /**
     * Must inject container for getting service contract.trans_union_reporting
     * such service exist only in RentTrack and CreditJeeves don't have it and will broken
     *
     * @Inject("service_container", required = true)
     */
    public $container;

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /**
         * @var $payment Payment
         */
        $payment = $eventArgs->getEntity();
        if (($payment instanceof Payment) === false) {
            return;
        }

        $payment->checkContract();
        $this->turnOnTransUnionReporting($eventArgs);
    }

    protected function turnOnTransUnionReporting(LifecycleEventArgs $eventArgs)
    {
        $payment = $eventArgs->getEntity();
        if (($payment instanceof Payment) === false) {
            return;
        }

        $contract = $payment->getContract();

        /**
         * $container->has = need for don't broken upload fixtures
         */
        if (!$contract || !$this->container->has('contract.trans_union_reporting')) {
            return;
        }

        /**
         * @var $tenant
         */
        $tenant = $contract->getTenant();

        if ($tenant->getIsVerified() !== UserIsVerified::PASSED) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $tuReporting = $this->container->get('contract.trans_union_reporting');
        $payments = $em->getRepository('RjDataBundle:Payment')->findByUser($tenant);

        if (count($payments) === 0 && $tuReporting->turnOnTransUnionReporting($contract)) {
            $em->flush($contract);
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if (($entity instanceof Contract) &&
            ContractStatus::DELETED == $entity->getStatus() &&
            ($payment = $entity->getActivePayment()) &&
            PaymentStatus::CLOSE != $payment->getStatus()
        ) {
            $payment->setClosed($this, PaymentCloseReason::CONTRACT_DELETED);
            $em->persist($payment);
            $em->flush($payment);
        }
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Payment) {
                $paramUpdate = [];
                if (PaymentStatus::CLOSE != $entity->getStatus()) {
                    $oldStatus = $entity->getStatus();
                    $entity->setClosed($this, PaymentCloseReason::DELETED);
                    $paramUpdate = [
                        'status' => [$oldStatus, PaymentStatus::CLOSE],
                        'closeDetails' => [null, $entity->getCloseDetails()],
                        'updatedAt' => [$entity->getUpdatedAt(), new DateTime()]
                    ];
                }
                // To avoid Payment entity deletion, we persist the entity (Entity becomes managed again)
                $em->persist($entity);
                $uow->scheduleExtraUpdate($entity, $paramUpdate);
            }
        }
    }
}
