<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PayDirectProcessorInterface;
use RentJeeves\DataBundle\Entity\OutboundTransaction;

class CheckSender
{
    /**
     * @var PayDirectProcessorInterface
     */
    protected $paymentProcessor;

    /**
     * @var OrderStatusManagerInterface
     */
    protected $statusManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PayDirectProcessorInterface $processor
     * @param OrderStatusManagerInterface $statusManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        PayDirectProcessorInterface $processor,
        OrderStatusManagerInterface $statusManager,
        LoggerInterface $logger
    ) {
        $this->paymentProcessor = $processor;
        $this->statusManager = $statusManager;
        $this->logger = $logger;
    }

    /**
     * @param OrderPayDirect $order
     *
     * @return bool
     */
    public function send(OrderPayDirect $order)
    {
        try {
            if ($this->paymentProcessor->executeOrder($order)) {
                $this->logger->debug(
                    sprintf(
                        '[CheckSender] Check for OrderPayDirect#%d has been sent successfully',
                        $order->getId()
                    )
                );
                $this->statusManager->setSending($order);

                return true;
            }
        } catch (\Exception $e) {
            $this->logger->emergency(sprintf(
                '[CheckSender] Get exception \'%s\' with message: %s',
                get_class($e),
                $e->getMessage()
            ));

            return false;
        }

        /** @var OutboundTransaction $outboundTransaction */
        $outboundTransaction = $order->getDepositOutboundTransaction();
        $this->statusManager->setError($order);
        $this->logger->alert(
            sprintf(
                '[CheckSender] Check for OrderPayDirect#%d has not been sent successfully. Reason: %s',
                $order->getId(),
                $outboundTransaction->getMessage()
            )
        );

        return false;
    }
}
