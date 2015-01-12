<?php

namespace RentJeeves\CheckoutBundle\Payment;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;

/**
 * @DI\Service("payment.deposit_report")
 */
class PaymentDepositReport implements PaymentSynchronizerInterface
{
    const REPORT_FILENAME_SUFFIX = 'ACHDepositsandChargesExport';

    protected $em;
    protected $repo;
    protected $fileReader;
    protected $fileFinder;
    /**
     * @var BusinessDaysCalculator
     */
    protected $businessDaysCalculator;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "fileReader" = @DI\Inject("reader.csv"),
     *     "fileFinder" = @DI\Inject("payment.report.finder"),
     *     "businessDaysCalc" = @DI\Inject("business_days_calculator")
     * })
     */
    public function __construct($em, $fileReader, $fileFinder, BusinessDaysCalculator $businessDaysCalc)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository('RjDataBundle:Heartland');
        $this->fileReader = $fileReader;
        $this->fileFinder = $fileFinder;
        $this->businessDaysCalculator = $businessDaysCalc;
    }

    public function synchronize($makeArchive = false)
    {
        if (!$file = $this->fileFinder->findBySuffix(self::REPORT_FILENAME_SUFFIX)) {
            return 0;
        }

        $data = $this->fileReader->read($file);

        foreach ($data as $paymentData) {
            $this->processDeposit($paymentData);
        }

        if ($makeArchive) {
            $this->fileFinder->archive($file, self::REPORT_FILENAME_SUFFIX);
        }

        return count($data);
    }

    public function processDeposit(array $paymentData)
    {
        /** @var HeartlandTransaction $transaction */
        $transaction = $this->repo->findOneByTransactionId($paymentData['TransactionID']);
        if (!$transaction) {
            return;
        }

        if (!empty($paymentData['BatchCloseDate'])) {
            $transaction->setBatchDate(new DateTime($paymentData['BatchCloseDate']));
        }

        if ($paymentData['MerchantDepositAmount'] > 0 && !empty($paymentData['MerchantDepositDate'])) {
            $transaction->getOrder()->setStatus(OrderStatus::COMPLETE);
            $merchantDepositDate = new DateTime($paymentData['MerchantDepositDate']);
            $depositDate = $this->businessDaysCalculator->getNextBusinessDate($merchantDepositDate);
            $transaction->setDepositDate($depositDate);
        }
        $this->em->flush();
    }
}
