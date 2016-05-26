<?php

namespace CreditJeeves\CoreBundle\Partner;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\DiExtraBundle\Annotation as DI;
use \RuntimeException;

/**
 * @DI\Service("partner.charging_manager")
 */
class PartnerChargingManager
{
    protected $container;
    protected $price;
    protected $product;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->price = $container->getParameter('partners')['price'];
        $this->product = $container->getParameter('partners')['product'];
    }

    public function charge(Order $order)
    {
        $partnerCode = $order->getUser() ? $order->getUser()->getPartnerCode() : null;
        if (!$partnerCode) {
            return false;
        }
        $partnerName = $partnerCode->getPartner()->getName();

        switch ($partnerName) {
            case 'creditcom':
                $partner = $this->container->get('partner.creditcom');
                break;
            default:
                throw new RuntimeException('Partner for charging not found');
        }

        return $partner->charge($partnerCode->getCode(), $this->price, $this->product, $order->getId());
    }
}
