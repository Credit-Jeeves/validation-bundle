<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\DependencyInjection\ContainerInterface;
use DateTime;
use RuntimeException;

class OrderListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Three main goals for this method:
     * 1. Set paidTo for contract
     * 2. Set daysLate for order
     * 3. Mark tenant as ready for charge
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $this->chargePartner($entity, $eventArgs->getEntityManager());
        }
    }

    /**
     * Why we need to use preUpdate event?
     * Because Order always(!!!) is created with status "NEWONE"
     * It will be changed after attempt of payment
     * 
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var Order $entity */
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $operation = $entity->getRentOperation();
            if (!$operation) {
                return;
            }
            $status = $entity->getStatus();
            switch ($status) {
                case OrderStatus::REFUNDED:
                case OrderStatus::CANCELLED:
                case OrderStatus::RETURNED:
                    if ($eventArgs->hasChangedField('status')
                        && !in_array(
                            $eventArgs->getOldValue('status'),
                            array(OrderStatus::REFUNDED, OrderStatus::CANCELLED, OrderStatus::RETURNED)
                        )
                    ) {
                        // Any changes to associations aren't flushed, that's why contract is flushed in postUpdate
                        $contract = $operation->getContract();
                        $contract->unshiftPaidTo($operation->getAmount());
                    }
                    break;
            }
        }
    }
    

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            $type = OperationType::RENT;
            $operation = $entity->getOperations()->last();
            $type = $operation ? $operation->getType(): $type;
            switch ($type) {
                case OperationType::RENT:
                    $status = $entity->getStatus();
                    switch ($status) {
                        case OrderStatus::PENDING:
                            $this->container->get('project.mailer')->sendPendingInfo($entity);
                            break;
                        case OrderStatus::COMPLETE:
                            $this->container->get('project.mailer')->sendRentReceipt($entity);
                            break;
                        case OrderStatus::ERROR:
                            $this->container->get('project.mailer')->sendRentError($entity);
                            break;
                        case OrderStatus::REFUNDED:
                        case OrderStatus::CANCELLED:
                        case OrderStatus::RETURNED:
                            // changes to contract are made in preUpdate since only there we can check whether the order
                            // status has been changed. But those changes aren't flushed. So the flush is here.
                            $contract = $operation->getContract();
                            $eventArgs->getEntityManager()->flush($contract);

                            $this->container->get('project.mailer')->sendOrderCancelToTenant($entity);
                            $this->container->get('project.mailer')->sendOrderCancelToLandlord($entity);
                        
                            break;
                    }
                    break;
                case OperationType::REPORT:
                    $status = $entity->getStatus();
                    switch ($status) {
                        case OrderStatus::COMPLETE:
                            $this->container->get('project.mailer')->sendReportReceipt($entity);
                            break;
                    }
                    break;
            }
        }
    }

    private function chargePartner(Order $order, EntityManager $em)
    {
        $operation = $order->getOperations()->last();
        if ($operation && $operation->getType() == OperationType::RENT) {
            /** @var User $user */
            $user = $order->getUser();
            $countOrders = count($user->getOrders());
            $partnerCode = $user->getPartnerCode();

            if ($countOrders == 0 && $partnerCode) {
                $partnerCode->setFirstPaymentDate(new DateTime());
            }
        }
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->updateStartAtOfContract($event);
    }

    /**
     * When tenant pays first time, set start_at = paid_for for first payment.
     * More description on this page https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     * See table Possible Paths
     *
     * @param LifecycleEventArgs $event
     */
    public function updateStartAtOfContract(LifecycleEventArgs $event)
    {
        /**
         * @var $order Order
         */
        $order = $event->getEntity();
        if (!($order instanceof Order)) {
            return;
        }
        $contract = $order->getContract();

        if (empty($contract)) {
            return;
        }

        $em = $event->getEntityManager();
        $operation = $em->getRepository('DataBundle:Operation')->findOneBy(
            array(
                'contract' => $contract->getId(),
            )
        );

        if ($order->getStatus() !== OrderStatus::COMPLETE || !empty($operation)) {
            return;
        }
        /**
         * @var $operation Operation
         */
        $operation = $order->getOperations()->first();
        $paidFor = $operation->getPaidFor();
        if ($operation->getType() !== OperationType::RENT || empty($paidFor)) {
            return;
        }

        $contract->setStartAt($paidFor);
        $em->flush($contract);
    }
}
