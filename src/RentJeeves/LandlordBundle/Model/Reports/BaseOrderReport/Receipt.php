<?php

namespace RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Receipt")
 */
class Receipt
{
    protected $totalAmount = 0;

    protected $isCash = false;

    /**
     * Check plus Heartland Transaction ID
     */
    protected $checkNumber;

    /**
     *  Date time of actual payment transaction with Heartland
     */
    protected $date;

    protected $notes;

    protected $payerName;

    protected $postMonth;

    /**
     * @Serializer\XmlList(inline = false, entry = "Detail")
     * @Serializer\XmlKeyValuePairs
     */
    protected $details = array();

    /**
     * @param DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $payerName
     */
    public function setPayerName($payerName)
    {
        $this->payerName = $payerName;
    }

    /**
     * @return string
     */
    public function getPayerName()
    {
        return $this->payerName;
    }

    /**
     * @param string $postMonth
     */
    public function setPostMonth($postMonth)
    {
        $this->postMonth = $postMonth;
    }

    /**
     * @return string
     */
    public function getPostMonth()
    {
        return $this->postMonth;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * @param string $checkNumber
     */
    public function setCheckNumber($checkNumber)
    {
        $this->checkNumber = $checkNumber;
    }

    /**
     * @return string
     */
    public function getCheckNumber()
    {
        return $this->checkNumber;
    }

    /**
     * @param boolean $isCash
     */
    public function setIsCash($isCash)
    {
        $this->isCash = $isCash;
    }

    /**
     * @return boolean
     */
    public function getIsCash()
    {
        return $this->isCash;
    }


    /**
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount = null)
    {
        if (!is_null($totalAmount)) {
            $this->totalAmount = $totalAmount;
        }

        foreach ($this->getDetails() as $detail) {
            $this->totalAmount += $detail->getAmount();
        }
    }

    /**
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param object $details
     */
    public function addDetails(Detail $detail)
    {
        $this->details[] = $detail;
    }

    /**
     * @return array Detail
     */
    public function getDetails()
    {
        return $this->details;
    }
}
