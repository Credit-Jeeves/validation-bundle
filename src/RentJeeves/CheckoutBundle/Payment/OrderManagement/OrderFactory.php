<?php

namespace RentJeeves\CheckoutBundle\Payment\OrderManagement;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

class OrderFactory
{
    /**
     * @param Group $group
     * @return OrderSubmerchant|OrderPayDirect
     */
    public static function getOrder(Group $group)
    {
        switch ($group->getOrderAlgorithm()) {
            case OrderAlgorithmType::PAYDIRECT:
                return new OrderPayDirect();
            default:
                return new OrderSubmerchant();
        }
    }
}
