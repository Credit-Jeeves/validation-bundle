<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Exception\CheckSenderException;
use RentJeeves\CheckoutBundle\PaymentProcessor\PayDirectProcessorInterface;
use RentJeeves\DataBundle\Entity\OutboundTransaction;

class CheckSender
{
    /**
     * @var PayDirectProcessorInterface
     */
    protected $paymentProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PayDirectProcessorInterface $processor
     * @param LoggerInterface $logger
     */
    public function __construct(PayDirectProcessorInterface $processor, LoggerInterface $logger)
    {
        $this->paymentProcessor = $processor;
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

                return true;
            }
        } catch (\Exception $e) {
            $this->logger->emergency($message = sprintf(
                '[CheckSender] Get exception \'%s\' with message: %s',
                get_class($e),
                $e->getMessage()
            ));
            throw new CheckSenderException($message);
        }

        /** @var OutboundTransaction $outboundTransaction */
        $outboundTransaction = $order->getDepositOutboundTransaction();
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
