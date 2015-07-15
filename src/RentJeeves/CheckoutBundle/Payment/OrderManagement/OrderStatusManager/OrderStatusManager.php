<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;

class OrderStatusManager implements OrderStatusManagerInterface
{
    /**
     * @var OrderSubmerchantStatusManager
     */
    protected $orderSubmerchantManager;

    /**
     * @var OrderPayDirectStatusManager
     */
    protected $orderPayDirectManager;

    /**
     * @param OrderSubmerchantStatusManager $orderSubmerchantManager
     * @param OrderPayDirectStatusManager $orderPayDirectManager
     */
    public function setManagers(
        OrderSubmerchantStatusManager $orderSubmerchantManager,
        OrderPayDirectStatusManager $orderPayDirectManager
    ) {
        $this->orderSubmerchantManager = $orderSubmerchantManager;
        $this->orderPayDirectManager = $orderPayDirectManager;
    }

    /**
     * @param Order $order
     * @return OrderStatusManagerInterface
     * @throws \InvalidArgumentException
     */
    final private function getManager(Order $order)
    {
        if ($order instanceof OrderSubmerchant) {
            return $this->orderSubmerchantManager;
        } elseif ($order instanceof OrderPayDirect) {
            return $this->orderPayDirectManager;
        } else {
            throw new \InvalidArgumentException('Invalid order type.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setComplete(Order $order)
    {
        $this->getManager($order)->setComplete($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setCancelled(Order $order)
    {
        $this->getManager($order)->setCancelled($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setRefunded(Order $order)
    {
        $this->getManager($order)->setRefunded($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setReturned(Order $order)
    {
        $this->getManager($order)->setReturned($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setPending(Order $order)
    {
        $this->getManager($order)->setPending($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setError(Order $order)
    {
        $this->getManager($order)->setError($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setNew(Order $order)
    {
        $this->getManager($order)->setNew($order);
    }
}
