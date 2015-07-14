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

}
