<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Serializer\Normalizer;

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
                ->setBatchCloseDate(new DateTime($transaction['BatchCloseDate']))
                ->setTransactionID($transaction['TransactionID'])
                ->setDepositAmount($transaction['MerchantDepositAmount'])
                ->setDepositDate(new DateTime($transaction['MerchantDepositDate']));

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
