<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Model\YardiSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="yardi_settings")
 */
class YardiSettings extends Base implements SettingsInterface
{
    const REVERSAL_PAYMENT_TYPE = 'Other';
    const REVERSAL_TRANSACTION_NSF = 'NSF';
    const REVERSAL_TRANSACTION_REVERSE = 'Reverse';
    const PAYMENT_TYPE_CASH = 'cash';
    const PAYMENT_TYPE_CHECK = 'check';
    const PAYMENT_TYPE_OTHER = 'other';

    public function getParameters()
    {
        return array(
            'url' => $this->getUrl()
        );
    }

    public function getOrderType(Order $order)
    {
        if (OrderType::HEARTLAND_BANK == $order->getType()) {
            return $this->getPaymentTypeACH();
        }
        if (OrderType::HEARTLAND_CARD == $order->getType()) {
            return $this->getPaymentTypeCC();
        }

        return null;
    }

    /**
     * When reversing a receipt of type “Cash” please use a reversal type of “Reverse”.
     * When reversing a receipt of type “Check” please use a reversal type of “NSF”
     *
     * @param Order $order
     * @return string|null
     */
    public function getReversalType(Order $order)
    {
        $originalOrderType = $this->getOrderType($order);
        if (strtolower($originalOrderType) == strtolower(self::PAYMENT_TYPE_CASH)) {
            return self::REVERSAL_TRANSACTION_REVERSE;
        }
        if (strtolower($originalOrderType) == strtolower(self::PAYMENT_TYPE_CHECK) ||
            strtolower($originalOrderType) == strtolower(self::PAYMENT_TYPE_OTHER)
        ) {
            return self::REVERSAL_TRANSACTION_NSF;
        }

        return null;
    }
}
