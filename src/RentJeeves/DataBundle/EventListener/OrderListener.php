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
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RentJeeves\CoreBundle\DateTime;
use RuntimeException;
use RentJeeves\DataBundle\Entity\Contract;

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
        if (!$entity instanceof Order) {
            return;
        }
        
        $operation = $entity->getRentOperation();
        if (!$operation || !$eventArgs->hasChangedField('status')) {
            return;
        }
        $contract = $operation->getContract();
        $this->updateBalanceContract($eventArgs);
        $movePaidFor = null;
        switch ($entity->getStatus()) {
            case OrderStatus::REFUNDED:
            case OrderStatus::CANCELLED:
            case OrderStatus::RETURNED:
                $contract->unshiftPaidTo($operation->getAmount());
                $movePaidFor = '-1';
                break;
            case OrderStatus::COMPLETE:
                $contract->shiftPaidTo($operation->getAmount());
                $movePaidFor = '+1';
                break;
        }

        if ($movePaidFor && ($payment = $operation->getContract()->getActivePayment())) {
            $date = new DateTime($payment->getPaidFor()->format('c'));
            $payment->setPaidFor($date->modify($movePaidFor . ' month'));
        }
        // Any changes to associations aren't flushed, that's why contract is flushed in postUpdate
    }
    

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            /** @var Operation $operation */
            $operation = $entity->getOperations()->last();
            $save = false;
            switch ($operation->getType()) {
                case OperationType::RENT:
                    $status = $entity->getStatus();
                    switch ($status) {
                        case OrderStatus::PENDING:
                            $this->container->get('project.mailer')->sendPendingInfo($entity);
                            break;
                        case OrderStatus::COMPLETE:
                            $save = true;
                            $this->container->get('project.mailer')->sendRentReceipt($entity);
                            break;
                        case OrderStatus::ERROR:
                            $this->container->get('project.mailer')->sendRentError($entity);
                            break;
                        case OrderStatus::REFUNDED:
                        case OrderStatus::CANCELLED:
                        case OrderStatus::RETURNED:
                            $save = true;
                            $this->container->get('project.mailer')->sendOrderCancelToTenant($entity);
                            $this->container->get('project.mailer')->sendOrderCancelToLandlord($entity);
                            break;
                    }
                    break;
                case OperationType::REPORT:
                    switch ($entity->getStatus()) {
                        case OrderStatus::COMPLETE:
                            $this->container->get('project.mailer')->sendReportReceipt($entity);
                            break;
                    }
                    break;
            }
            if ($save) {
                // changes to contract are made in preUpdate since only there we can check whether the order
                // status has been changed. But those changes aren't flushed. So the flush is here.
                $eventArgs->getEntityManager()->flush($operation->getContract());
                if ($payment = $operation->getContract()->getActivePayment()) {
                    $eventArgs->getEntityManager()->flush($payment);
                }
            }
        }
    }

    private function chargePartner(Order $order, EntityManager $em)
    {
        $operation = $order->getRentOperation();
        if ($operation) {
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

        /**
         * If we have operation for particular contract it's means we already pay
         * so we must do not change it
         */
        if (!empty($operation)) {
            return;
        }

        /**
         * @var $operation Operation
         */
        $operation = $order->getOperations()->first();
        $paidFor = $operation->getPaidFor();
        if (empty($paidFor)) {
            return;
        }

        $contract->setStartAt($paidFor);
        $em->flush($contract);
    }

    public function updateBalanceContract(LifecycleEventArgs $eventArgs)
    {
        if (!$eventArgs->hasChangedField('status')) {
            return;
        }

        /**
         * @var $order Order
         */
        $order = $eventArgs->getEntity();

        /**
         * @var $contract Contract
         */
        $contract = $order->getContract();
        if (!$contract) {
            return;
        }

        if ($contract->getStatus() !== ContractStatus::CURRENT) {
            return;
        }

        $group = $contract->getGroup();
        $isIntegrated = $group->getGroupSettings()->getIsIntegrated();
        $em = $eventArgs->getEntityManager();
        $operations = $order->getOperations();
        switch ($order->getStatus()) {
            //Order complete so we must make minus
            case OrderStatus::COMPLETE:
                /**
                 * @var $operation Operation
                 */
                foreach ($operations as $operation) {
                    if ($operation->getType() === OperationType::RENT) {
                        $contract->setBalance($contract->getBalance() - $operation->getAmount());
                    }

                    if ($isIntegrated &&
                        in_array(
                            $operation->getType(),
                            array(
                                OperationType::RENT,
                                OperationType::OTHER
                            )
                        )
                    ) {
                        $contract->setIntegratedBalance($contract->getIntegratedBalance() - $operation->getAmount());
                    }
                }
                break;
            //Order comeback so we must make plus
            case OrderStatus::RETURNED:
            case OrderStatus::REFUNDED:
                /**
                 * @var $operation Operation
                 */
                foreach ($operations as $operation) {
                    if ($operation->getType() === OperationType::RENT) {
                        $contract->setBalance($contract->getBalance() + $operation->getAmount());
                    }

                    if ($isIntegrated &&
                        in_array(
                            $operation->getType(),
                            array(
                                OperationType::RENT,
                                OperationType::OTHER
                            )
                        )
                    ) {
                        $contract->setIntegratedBalance($contract->getIntegratedBalance() + $operation->getAmount());
                    }
                }
                break;
        }
    }
}
