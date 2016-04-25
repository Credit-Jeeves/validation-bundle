<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
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

    /**
     * {@inheritdoc}
     */
    public function isAllowedToSendRealTimePayments()
    {
        return $this->getSynchronizationStrategy() === SynchronizationStrategy::REAL_TIME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array(
            'url' => $this->getUrl()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiProperty()
    {
        return false;
    }

    /**
     * @param Order $order
     *
     * @return null|string
     */
    public function getOrderPaymentType(Order $order)
    {
        if (OrderPaymentType::BANK == $order->getPaymentType()) {
            return $this->getPaymentTypeACH();
        }
        if (OrderPaymentType::CARD == $order->getPaymentType()) {
            return $this->getPaymentTypeCC();
        }
        if (OrderPaymentType::SCANNED_CHECK === $order->getPaymentType()) {
            return $this->getPaymentTypeScannedCheck();
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
        if (OrderPaymentType::SCANNED_CHECK === $order->getPaymentType()) {
            return self::REVERSAL_TRANSACTION_NSF;
        }

        $originalOrderType = $this->getOrderPaymentType($order);
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
