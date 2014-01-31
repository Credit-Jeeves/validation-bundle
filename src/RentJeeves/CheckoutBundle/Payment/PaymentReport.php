<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Heartland;

/**
 * @DI\Service("payment.report")
 */
class PaymentReport 
{
    protected $em;
    protected $repo;
    protected $fileReader;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileReader" = @DI\Inject("reader.csv"),
     * })
     */
    public function __construct($em, $fileReader)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository('RjDataBundle:Heartland');
        $this->fileReader = $fileReader;
    }

    public function synchronize($report)
    {
        $data = $this->fileReader->read($report);

        foreach ($data as $paymentData) {
            switch ($paymentData['TransactionType']) {
                case 'Payment':
                    $transaction = $this->processCompletePayment($paymentData);
                    break;
                case 'Payment Return':
                    $transaction = $this->processReturnedPayment($paymentData);
                    break;
            }

            if ($transaction && $batchId = $paymentData['BatchID']) {
                $transaction->setBatchId($batchId);
            }
            $this->em->flush();
        }
    }

    protected function processCompletePayment($paymentData)
    {
        $transactionId = $paymentData['TransactionID'];
        $transaction = $this->repo->findOneBy(array('transactionId' => $transactionId));

        return $transaction;
    }

    protected function processReturnedPayment($paymentData)
    {
        $transactionId = $paymentData['OriginalTransactionID'];
        $transaction = $this->repo->findOneBy(array('transactionId' => $transactionId));

        if (!$transaction) {
            return null;
        }

        $order = $transaction->getOrder();
        $order->setStatus(OrderStatus::RETURNED);

        return $transaction;
    }
} 
