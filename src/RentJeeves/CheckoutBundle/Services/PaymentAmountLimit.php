<?php

namespace RentJeeves\CheckoutBundle\Services;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Payment;

class PaymentAmountLimit
{
    /**
     * @var int
     */
    protected $paymentMaxLimit;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param int $limit
     * @param LoggerInterface $logger
     */
    public function __construct($limit, LoggerInterface $logger)
    {
        $this->paymentMaxLimit = $limit;
        $this->logger = $logger;
    }

    /**
     * @param Payment $payment
     * @param bool $isAlert
     * @return bool
     */
    public function checkIfExceedsMax(Payment $payment, $isAlert = true)
    {
        if ($payment->getTotal() > $this->paymentMaxLimit) {
            if ($isAlert) {
                $this->logger->alert(sprintf(
                    'Payment total %d of payment #%d is greater than max limit %d',
                    $payment->getTotal(),
                    $payment->getId(),
                    $this->paymentMaxLimit
                ));
            }

            return true;
        }

        return false;
    }
}
