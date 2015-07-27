<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Order;

interface OrderStatusManagerInterface
{
    /**
     * @param Order $order
     */
    public function setReissued(Order $order);

    /**
     * @param Order $order
     */
    public function setSending(Order $order);

    /**
     * @param Order $order
     */
    public function setComplete(Order $order);

    /**
     * @param Order $order
     */
    public function setCancelled(Order $order);

    /**
     * @param Order $order
     */
    public function setRefunded(Order $order);

    /**
     * @param Order $order
     */
    public function setReturned(Order $order);

    /**
     * @param Order $order
     */
    public function setPending(Order $order);

    /**
     * @param Order $order
     */
    public function setError(Order $order);

    /**
     * @param Order $order
     */
    public function setNew(Order $order);
}
