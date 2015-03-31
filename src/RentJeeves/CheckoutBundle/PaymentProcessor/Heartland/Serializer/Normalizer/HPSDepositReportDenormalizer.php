<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Normalizer;

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
        $result = [];
        foreach ($data as $transaction) {
            $depositTransaction = new DepositReportTransaction();
            $depositTransaction
                ->setBatchId($transaction['BatchID'])
                ->setTransactionId($transaction['TransactionID'])
                ->setDepositAmount($transaction['MerchantDepositAmount']);

            $batchDate = $transaction['BatchCloseDate'];
            if (!empty($batchDate)) {
                $depositTransaction->setBatchCloseDate(new DateTime($batchDate));
            }

            $depositDate = $transaction['MerchantDepositDate'];
            if (!empty($depositDate)) {
                $depositTransaction->setDepositDate(new DateTime($depositDate));
            }

            $result[] = $depositTransaction;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::FORMAT === $format && is_array($data);
    }
}
