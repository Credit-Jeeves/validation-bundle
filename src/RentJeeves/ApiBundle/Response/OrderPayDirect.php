<?php

namespace RentJeeves\ApiBundle\Response;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;

/**
 * @DI\Service("response_resource.order_paydirect")
 * @UrlResourceMeta(
 *      actionName = "get_order"
 * )
 */
class OrderPayDirect extends Order
{
    /**
     * @return string
     */
    public function getDepositedAt()
    {
        if ($transaction = $this->entity->getDepositOutboundTransaction() and $date = $transaction->getDepositDate()) {
            return $date->format('Y-m-d');
        }

        return '';
    }
}
