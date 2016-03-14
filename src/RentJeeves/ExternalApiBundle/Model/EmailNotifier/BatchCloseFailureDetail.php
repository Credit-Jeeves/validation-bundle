<?php

namespace RentJeeves\ExternalApiBundle\Model\EmailNotifier;

class BatchCloseFailureDetail
{
    /**
     * @var string
     */
    protected $residentId;

    /**
     * @var string
     */
    protected $residentName;

    /**
     * @var \DateTime
     */
    protected $paymentDate;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $rentTrackBatchNumber;

    /**
     * @var string
     */
    protected $accountingSystemBatchNumber;

    /**
     * @return string
     */
    public function getAccountingSystemBatchNumber()
    {
        return $this->accountingSystemBatchNumber;
    }

    /**
     * @param string $accountingSystemBatchNumber
     */
    public function setAccountingSystemBatchNumber($accountingSystemBatchNumber)
    {
        $this->accountingSystemBatchNumber = $accountingSystemBatchNumber;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return string
     */
    public function getRentTrackBatchNumber()
    {
        return $this->rentTrackBatchNumber;
    }

    /**
     * @param string $rentTrackBatchNumber
     */
    public function setRentTrackBatchNumber($rentTrackBatchNumber)
    {
        $this->rentTrackBatchNumber = $rentTrackBatchNumber;
    }

    /**
     * @return string
     */
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }

    /**
     * @return string
     */
    public function getResidentName()
    {
        return $this->residentName;
    }

    /**
     * @param string $residentName
     */
    public function setResidentName($residentName)
    {
        $this->residentName = $residentName;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getPaymentDateFormatted()
    {
        return $this->getPaymentDate()->format('m/d/Y');
    }
}
