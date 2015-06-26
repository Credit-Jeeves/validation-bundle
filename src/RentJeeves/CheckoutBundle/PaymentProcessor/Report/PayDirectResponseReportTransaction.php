<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Report;

class PayDirectResponseReportTransaction extends PaymentProcessorReportTransaction
{
    const PAY_DIRECT_RESPONSE_STATUS = 'READY TO DISBURSE';

    /**
     * @var \DateTime
     */
    protected $batchCloseDate;

    /**
     * @var string
     */
    protected $responseCode;

    /**
     * @var string
     */
    protected $responseMessage;

    /**
     * @return \DateTime
     */
    public function getBatchCloseDate()
    {
        return $this->batchCloseDate;
    }

    /**
     * @param \DateTime $batchCloseDate
     */
    public function setBatchCloseDate($batchCloseDate)
    {
        $this->batchCloseDate = $batchCloseDate;
    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param string $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param string $responseMessage
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }
}
