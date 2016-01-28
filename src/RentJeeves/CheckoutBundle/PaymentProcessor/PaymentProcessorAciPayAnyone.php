<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;

/**
 * @DI\Service("payment_processor.aci_pay_anyone")
 */
class PaymentProcessorAciPayAnyone implements PayDirectProcessorInterface
{
    const DELIVERY_BUSINESS_DAYS_FOR_BANK = 10;
    const DELIVERY_BUSINESS_DAYS_FOR_CARD = 10;

    /**
     * @var PaymentManager
     */
    protected $paymentManager;

    /**
     * @var ReportLoader
     */
    protected $reportLoader;

    /**
     * @param PaymentManager $paymentManager
     * @param ReportLoader $reportLoader
     *
     * @DI\InjectParams({
     *     "paymentManager" = @DI\Inject("payment_processor.aci.pay_anyone.payment_manager"),
     *     "reportLoader" = @DI\Inject("payment_processor.aci.pay_anyone.report_loader")
     * })
     */
    public function __construct(PaymentManager $paymentManager, ReportLoader $reportLoader)
    {
        $this->paymentManager = $paymentManager;

        $this->reportLoader = $reportLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ACIPayAnyone';
    }

    /**
     * {@inheritdoc}
     */
    public function generateReversedBatchId(Order $order)
    {
        // will be implemented later
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function executeOrder(OrderPayDirect $order)
    {
        if (!$this->isAllowedToExecuteOrder($order)) {
            throw new PaymentProcessorInvalidArgumentException('Order can\'t be executed');
        }

        return $this->paymentManager->executePayment($order);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(OrderPayDirect $order)
    {
        if (!$order->getDepositOutboundTransaction() || !$order->getDepositOutboundTransaction()->getTransactionId()) {
            throw new PaymentProcessorInvalidArgumentException('Order doesn\'t have successful outbound transaction');
        }

        return $this->paymentManager->cancelPayment($order);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        return $this->reportLoader->loadReport();
    }

    /**
     * @param OrderPayDirect $order
     * @return bool
     */
    protected function isAllowedToExecuteOrder(OrderPayDirect $order)
    {
        // TODO Need validate by order type
        return true;
    }
}
