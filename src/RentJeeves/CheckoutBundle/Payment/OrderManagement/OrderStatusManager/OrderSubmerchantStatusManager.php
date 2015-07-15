<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class OrderSubmerchantStatusManager implements OrderStatusManagerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @param EntityManager $em
     * @param LoggerInterface $logger
     * @param Mailer $mailer
     */
    public function __construct(EntityManager $em, LoggerInterface $logger, Mailer $mailer)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    /**
     * @param Order $order
     */
    public function setComplete(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::COMPLETE)) {
            $this->syncTransactions($order);
            $this->updateBalanceContract($order);
            $this->movePaidDates($order);
            /** @var Operation $operation */
            $operation = $order->getOperations()->last();

            if (!$operation) {
                return;
            }

            if (in_array($operation->getType(), [OperationType::RENT, OperationType::OTHER])) {
                $this->mailer->sendRentReceipt($order);
            } elseif ($operation->getType() === OperationType::REPORT) {
                $this->mailer->sendReportReceipt($order);
            }
        }
    }

    /**
     * @param Order $order
     */
    public function setCancelled(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::CANCELLED)) {
            $this->movePaidDates($order, false);
            $this->sendReversalEmail($order);
        }
    }

    /**
     * @param Order $order
     */
    public function setRefunded(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::REFUNDED)) {
            $this->updateBalanceContract($order, false);
            $this->movePaidDates($order, false);
            $this->sendReversalEmail($order);
        }
    }

    /**
     * @param Order $order
     */
    public function setReturned(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::RETURNED)) {
            $this->updateBalanceContract($order, false);
            $this->movePaidDates($order, false);
            // if returned order is from recurring payment, close that payment!
            $this->closeRecurringPayment($order);
            $this->sendReversalEmail($order);
        }
    }

    /**
     * @param Order $order
     */
    public function setPending(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::PENDING)) {
            $this->mailer->sendPendingInfo($order);
        }
    }

    /**
     * @param Order $order
     */
    public function setError(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::ERROR)) {
            $this->mailer->sendRentError($order);
        }
    }

    /**
     * @param Order $order
     */
    public function setNew(Order $order)
    {
        if ($this->updateStatus($order, OrderStatus::NEWONE)) {
            $this->chargePartner($order);
            if ($order->getContract()) {
                $this->updateStartAtOfContract($order->getContract());
            }
        }
    }

    /**
     * @param Order $order
     * @param string $oderStatus
     * @return bool
     */
    protected function updateStatus(Order $order, $oderStatus)
    {
        if (!OrderStatus::isValid($oderStatus)) {
            throw new \InvalidArgumentException(sprintf('Order status "%s" is invalid', $oderStatus));
        }

        if ($order->getStatus() === $oderStatus) {
            return false;
        }

        $order->setStatus($oderStatus);

        if (!$this->em->contains($order)) {
            $this->em->persist($order);
        }

        $this->em->flush($order);

        return true;
    }

    /**
     * @param Order $order
     */
    protected function chargePartner(Order $order)
    {
        $operation = $order->getRentOperations()->first();

        if ($operation) {
            $user = $order->getUser();

            if ($user->getOrders()->count() == 0 && $user->getPartnerCode()) {
                $user->getPartnerCode()->setFirstPaymentDate(new \DateTime());
            }

            $this->em->flush($user->getPartnerCode());
        }
    }

    /**
     * @param Order $order
     * @param bool $shift
     */
    protected function movePaidDates(Order $order, $shift = true)
    {
        $operations = $order->getRentOperations();

        foreach ($operations as $operation) {
            /** @var Operation $operation */
            $contract = $operation->getContract();

            if ($shift) {
                $contract->shiftPaidTo($operation->getAmount());
                $movePaidFor = '+1';
            } else {
                $contract->unshiftPaidTo($operation->getAmount());
                $movePaidFor = '-1';
            }

            if ($payment = $contract->getActivePayment()) {
                $date = new DateTime($payment->getPaidFor()->format('c'));
                $paidFor = $date->modify($movePaidFor . ' month');
                $payment->setPaidFor($paidFor);
            }

            if (!$this->em->contains($contract)) {
                $this->em->persist($contract);
            }

            if (!$this->em->contains($payment)) {
                $this->em->persist($payment);
            }
        }

        $this->em->flush();
    }

    /**
     * @param Order $order
     * @param bool $isSubtract
     */
    protected function updateBalanceContract(Order $order, $isSubtract = true)
    {
        $contract = $order->getContract();
        $isIntegrated = $contract->getGroup()->getGroupSettings()->getIsIntegrated();
        $operations = $order->getOperations();

        foreach ($operations as $operation) {
            if ($operation->getType() === OperationType::RENT) {
                if ($isSubtract) {
                    $balance = $contract->getBalance() - $operation->getAmount();
                } else {
                    $balance = $contract->getBalance() + $operation->getAmount();
                }

                $contract->setBalance($balance);
            }

            if ($isIntegrated && in_array($operation->getType(), [OperationType::RENT, OperationType::OTHER])) {
                if ($isSubtract) {
                    $balance = $contract->getIntegratedBalance() - $operation->getAmount();
                } else {
                    $balance = $contract->getIntegratedBalance() + $operation->getAmount();
                }

                $contract->setIntegratedBalance($balance);
            }
        }

        if (!$this->em->contains($contract)) {
            $this->em->persist($contract);
        }

        $this->em->flush($contract);
    }

    /**
     * When tenant pays first time, set start_at = paid_for for first payment.
     * More description on this page https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     * See table Possible Paths
     *
     * @param Order $order
     * @return bool
     */
    protected function updateStartAtOfContract(Order $order)
    {
        $contract = $order->getContract();

        $rentOperation = $this->em->getRepository('DataBundle:Operation')->findOneBy([
            'contract' => $contract->getId(),
            'type' => OperationType::RENT
        ]);

        /**
         * If we have RENT operation for particular contract it's means we already pay
         * so we must do not change it
         */
        if (!$rentOperation) {
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

        if (!$earliestOperation->getPaidFor() || $contract->getStartAt() === $earliestOperation->getPaidFor()) {
            return false;
        }

        $contract->setStartAt($earliestOperation->getPaidFor());

        if (!$this->em->contains($contract)) {
            $this->em->persist($contract);
        }
        $this->em->flush($contract);

        return true;
    }

    /**
     * @param Order $order
     */
    protected function syncTransactions(Order $order)
    {
        $transaction = $order->getCompleteTransaction();

        if ($transaction
            && OrderType::HEARTLAND_CARD == $order->getType()
            && PaymentProcessor::HEARTLAND === $order->getPaymentProcessor()
        ) {
            $batchDate = clone $transaction->getCreatedAt();
            $transaction->setBatchDate($batchDate);
            $transaction->setDepositDate(BusinessDaysCalculator::getNextBusinessDate(clone $batchDate));

            if (!$this->em->contains($transaction)) {
                $this->em->persist($transaction);
            }
            $this->em->flush($transaction);
        }
    }

    /**
     * @param Order $order
     */
    protected function closeRecurringPayment(Order $order)
    {
        if (OrderType::HEARTLAND_BANK != $order->getType()) {
            return;
        }

        $contract = $order->getContract();
        if (!$contract) {
            return;
        }

        $payment = $contract->getActivePayment();
        if (!$payment) {
            return;
        }

        if ($payment->isRecurring()) {
            $payment->setClosed($this, PaymentCloseReason::RECURRING_RETURNED);

            if (!$this->em->contains($payment)) {
                $this->em->persist($payment);
            }
            $this->em->flush($payment);
        }
    }

    /**
     * @param Order $order
     */
    protected function sendReversalEmail(Order $order)
    {
        /** @var Operation $operation */
        $operation = $order->getOperations()->last();

        if (!$operation) {
            return;
        }
        if ($order->getType() != OrderType::CASH &&
            in_array($operation->getType(), [OperationType::RENT, OperationType::OTHER])
        ) {
            $this->mailer->sendOrderCancelToTenant($order);
            $this->mailer->sendOrderCancelToLandlord($order);
        }
    }
}
