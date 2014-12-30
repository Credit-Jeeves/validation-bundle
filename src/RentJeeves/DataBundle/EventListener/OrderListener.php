<?php
namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RentJeeves\CoreBundle\DateTime;
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
            $this->chargePartner($entity);
            $this->updateStartAtOfContract($eventArgs);
        }
    }

    /**
     * Why we need to use preUpdate event?
     * Because Order always(!!!) is created with status "NEWONE"
     * It will be changed after attempt of payment
     * 
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        /** @var Order $entity */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Order) {
            return;
        }

        if (!$eventArgs->hasChangedField('status')) {
            return;
        }

        $this->syncTransactions($entity);

        $operations = $entity->getRentOperations();
        if ($operations->count() == 0) {
            return;
        }

        $this->updateBalanceContract($eventArgs);

        /** @var Operation $operation */
        foreach ($operations as $operation) {
            $contract = $operation->getContract();
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
        }
        // Any changes to associations aren't flushed, that's why contract is flushed in postUpdate
    }


    private function updateStartAtOfContract($eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $order = $eventArgs->getEntity();

        if (!$startAt = $this->getStartAtOfContract($order, $em)) {
            return;
        }

        $contract = $order->getContract();
        $oldValue = $contract->getStartAt();
        $contract->setStartAt($startAt);
        $em->persist($contract);
        $uow->propertyChanged($contract, 'startAt', $oldValue, $startAt);
        $uow->scheduleExtraUpdate($contract, array('startAt' => array($oldValue, $startAt)));
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $order = $eventArgs->getEntity();
        if (!$order instanceof Order) {
            return;
        }

        $this->updateStartAtOfContract($eventArgs);
        /** @var Operation $operation */
        $operation = $order->getOperations()->last();
        if (!$operation) {
            return;
        }
        $save = false;
        switch ($operation->getType()) {
            case OperationType::RENT:
            case OperationType::OTHER:
                $status = $order->getStatus();
                switch ($status) {
                    case OrderStatus::PENDING:
                        $this->container->get('project.mailer')->sendPendingInfo($order);
                        break;
                    case OrderStatus::COMPLETE:
                        $save = true;
                        $this->container->get('project.mailer')->sendRentReceipt($order);
                        break;
                    case OrderStatus::ERROR:
                        $this->container->get('project.mailer')->sendRentError($order);
                        break;
                    case OrderStatus::REFUNDED:
                    case OrderStatus::CANCELLED:
                    case OrderStatus::RETURNED:
                        $save = true;
                        $this->container->get('project.mailer')->sendOrderCancelToTenant($order);
                        $this->container->get('project.mailer')->sendOrderCancelToLandlord($order);
                        break;
                }
                break;
            case OperationType::REPORT:
                switch ($order->getStatus()) {
                    case OrderStatus::COMPLETE:
                        $this->container->get('project.mailer')->sendReportReceipt($order);
                        break;
                }
                break;
        }

        if ($save) {
            // changes to contract are made in preUpdate since only there we can check whether the order
            // status has been changed. But those changes aren't flushed. So the flush is here.
            $eventArgs->getEntityManager()->flush($operation->getContract());
            $eventArgs->getEntityManager()->flush($order->getCompleteTransaction());
            if ($payment = $operation->getContract()->getActivePayment()) {
                $eventArgs->getEntityManager()->flush($payment);
            }
        }
    }

    private function chargePartner(Order $order)
    {
        $operation = $order->getRentOperations()->first();
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

    /**
     * When tenant pays first time, set start_at = paid_for for first payment.
     * More description on this page https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     * See table Possible Paths
     *
     * @param Order $order
     * @param EntityManager $em
     */
    private function getStartAtOfContract(Order $order, EntityManager $em)
    {
        $contract = $order->getContract();

        if (empty($contract)) {
            return false;
        }

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
            return false;
        }

        $rentOperations = $order->getRentOperations();
        /**
         * Start_at can be updated only if order contains RENT operations
         */
        if (!$rentOperations->count()) {
            return false;
        }
        /** @var Operation $earliestOperation */
        $earliestOperation = $rentOperations->first();
        foreach ($rentOperations as $rent) {
            if ($earliestOperation->getPaidFor() > $rent->getPaidFor()) {
                $earliestOperation = $rent;
            }
        }

        return $earliestOperation->getPaidFor();
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

        // Contract can be finished but last payment does not pass
//        if ($contract->getStatus() !== ContractStatus::CURRENT) {
//            return;
//        }

        $group = $contract->getGroup();
        $isIntegrated = $group->getGroupSettings()->getIsIntegrated();
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

    protected function syncTransactions(Order $order)
    {
        $transaction = $order->getCompleteTransaction();

        if ($transaction &&
            OrderType::HEARTLAND_CARD == $order->getType() &&
            OrderStatus::COMPLETE == $order->getStatus()
        ) {
            $batchDate = clone $transaction->getCreatedAt();
            $transaction->setBatchDate($batchDate);
            $businessDaysCalc = $this->container->get('business_days_calculator');
            $transaction->setDepositDate($businessDaysCalc->getNextBusinessDate(clone $batchDate));
        }
    }
}
