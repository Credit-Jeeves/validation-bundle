<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Normalizer;

use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HPSDepositReportDenormalizer implements DenormalizerInterface
{
    const FORMAT = 'hps_csv_file';

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $report = new DepositReport();

        foreach ($data as $transaction) {
            $depositTransaction = new DepositReportTransaction();
            $depositTransaction
                ->setBatchID($transaction['BatchID'])
                ->setTransactionID($transaction['TransactionID'])
                ->setDepositAmount($transaction['MerchantDepositAmount']);

            $batchDate = $transaction['BatchCloseDate'];
            if (!empty($batchDate)) {
                $depositTransaction->setBatchCloseDate(new DateTime($batchDate));
            }

            $depositDate = $transaction['MerchantDepositDate'];
            if (!empty($depositDate)) {
                $depositTransaction->setDepositDate(new DateTime($depositDate));
            }

            $report->addTransaction($depositTransaction);
        }

        return $report;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::FORMAT === $format && is_array($data);
    }
}
