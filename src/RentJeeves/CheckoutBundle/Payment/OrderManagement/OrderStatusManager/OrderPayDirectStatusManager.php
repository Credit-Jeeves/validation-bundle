<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Order;

class OrderPayDirectStatusManager extends OrderSubmerchantStatusManager
{
    /**
     * @param Order $order
     */
    public function setComplete(Order $order)
    {
        // TODO: Implement setComplete() method.
    }

    /**
     * @param Order $order
     */
    public function setCancelled(Order $order)
    {
        // TODO: Implement setCancelled() method.
    }

    /**
     * @param Order $order
     */
    public function setRefunded(Order $order)
    {
        // TODO: Implement setRefunded() method.
    }

    /**
     * @param Order $order
     */
    public function setReturned(Order $order)
    {
        // TODO: Implement setReturned() method.
    }

    /**
     * @param Order $order
     */
    public function setPending(Order $order)
    {
        // TODO: Implement setPending() method.
    }

    /**
     * @param Order $order
     */
    public function setError(Order $order)
    {
        // TODO: Implement setError() method.
    }

    /**
     * @param Order $order
     */
    public function setNew(Order $order)
    {
        // TODO: Implement setNew() method.
    }
}
