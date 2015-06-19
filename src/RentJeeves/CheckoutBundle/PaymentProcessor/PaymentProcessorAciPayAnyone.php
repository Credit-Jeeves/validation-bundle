<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;

/**
 * @DI\Service("payment_processor.aci_pay_anyone")
 */
class PaymentProcessorAciPayAnyone implements PayDirectProcessorInterface
{
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
    public function executeOrder(Order $order)
    {
        if (!$this->isAllowedToExecuteOrder($order)) {
            throw new PaymentProcessorInvalidArgumentException('Order can\'t executed');
        }

        return $this->paymentManager->executePayment($order);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(Order $order)
    {
        if (!$order->getDepositOutboundTransaction()) {
            throw new PaymentProcessorInvalidArgumentException('Order does have a outbound transaction');
        }

        $this->paymentManager->cancelPayment($order);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReport()
    {
        return $this->reportLoader->loadReport();
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function isAllowedToExecuteOrder(Order $order)
    {
        // TODO Need validate by order type
        return true;
    }
}
