<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;

class OrderPayDirectStatusManager extends OrderSubmerchantStatusManager
{
    /**
     * {@inheritdoc}
     */
    public function setSending(Order $order)
    {
        $this->assertOrder($order);

        if ($this->updateStatus($order, OrderStatus::SENDING)) {
            $this->mailer->sendOrderSendingNotification($order);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setComplete(Order $order)
    {
        $this->assertOrder($order);

        /** @var OrderPayDirect $order */
        if ($this->isOutboundLegInitiated($order)) {
            $this->updateStatus($order, OrderStatus::COMPLETE);
        } else {
            $this->initiateOutboundLeg($order);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCancelled(Order $order)
    {
        $this->assertOrder($order);

        /** @var OrderPayDirect $order */
        if ($this->isOutboundLegInitiated($order)) {
            $this->logger->alert(sprintf(
                'An attempt to cancel outbound transaction #%s of PayDirect order #%d.
                PayDirect order can not be cancelled (only reissued or refunded)',
                $order->getDepositOutboundTransaction()->getTransactionId(),
                $order->getId()
            ));
        } else {
            parent::setCancelled($order);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRefunded(Order $order)
    {
        $this->assertOrder($order);

        // if inbound leg is already returned, don't change order status
        if (OrderStatus::RETURNED == $order->getStatus()) {
            return;
        }

        /** @var OrderPayDirect $order */
        if ($this->isOutboundLegReversed($order)) {
            parent::setRefunded($order);
        } elseif ($this->updateStatus($order, OrderStatus::REFUNDING)) {
            $this->mailer->sendOrderRefundingNotification($order);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setReturned(Order $order)
    {
        $this->assertOrder($order);

        /** @var OrderPayDirect $order */
        if ($this->isOutboundLegInitiated($order)) {
            $this->logger->emergency(sprintf(
                'PayDirect order <%d> has a return, please goto PayAnyone CSI Console and stop check <%s> immediately',
                $order->getId(),
                $order->getDepositOutboundTransaction()->getTransactionId()
            ));
        }

        parent::setReturned($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setReissued(Order $order)
    {
        $this->assertOrder($order);

        if ($this->updateStatus($order, OrderStatus::REISSUED)) {
            $this->mailer->sendOrderReissuedNotification($order);
        }
    }

    /**
     * @param OrderPayDirect $order
     * @return bool
     */
    protected function isOutboundLegInitiated(OrderPayDirect $order)
    {
        return ($order->getDepositOutboundTransaction() instanceof OutboundTransaction) &&
        (OutboundTransactionStatus::SUCCESS === $order->getDepositOutboundTransaction()->getStatus());
    }

    /**
     * @param OrderPayDirect $order
     * @return bool
     */
    protected function isOutboundLegReversed(OrderPayDirect $order)
    {
        return $order->getReversalOutboundTransaction() instanceof OutboundTransaction;
    }

    /**
     * @param Order $order
     */
    protected function assertOrder(Order $order)
    {
        if (!$order instanceof OrderPayDirect) {
            throw new \LogicException('Unsupported order type in OrderPayDirectStatusManager');
        }
    }

    /**
     * @TODO: change it in RT-1671
     * @link https://credit.atlassian.net/browse/RT-1671
     *
     * @param Order $order
     */
    protected function initiateOutboundLeg(Order $order)
    {
//        $job = new Job('payment:pay-anyone:send-check', ['--app=rj', $order->getId()]);
//        $this->em->persist($job);
//        $this->em->flush($job);
    }
}
