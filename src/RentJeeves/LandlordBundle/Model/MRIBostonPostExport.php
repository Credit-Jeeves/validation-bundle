<?php

namespace RentJeeves\LandlordBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;

class MRIBostonPostExport
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("household_id")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getHouseholdId()
    {
        return $this->order->getContract()->getExternalLeaseId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("unit_id")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getUnitId()
    {
        if ($this->order->getContract()->getUnit()->getUnitMapping()) {
            return $this->order->getContract()->getUnit()->getUnitMapping()->getExternalUnitId();
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("total_pmt_amt")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     *
     * @return float
     */
    public function getSum()
    {
        $sum = $this->order->getSum();

        return number_format($sum, '2', '.', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("due_date")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getDueDate()
    {
        $completeTransaction = $this->order->getCompleteTransaction();

        if ($completeTransaction && $completeTransaction->getDepositDate()) {
            return $this->order->getTransaction()->getDepositDate()->format('mdY');
        }

        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Route Number")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getRouteNumber()
    {
        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Account Number")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return '';
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Check Number")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return int
     */
    public function getCheckNumber()
    {
        return $this->order->getTransactionId();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Check Amount")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return float
     */
    public function getCheckAmount()
    {
        $sum = $this->order->getSum();

        return number_format($sum, '2', '', '');
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Deposit Date")
     * @Serializer\Groups({"mri_bostonpost"})
     *
     * @return string
     */
    public function getDepositDate()
    {
        $completeTransaction = $this->order->getCompleteTransaction();

        if ($completeTransaction && $completeTransaction->getDepositDate()) {
            return $this->order->getTransaction()->getDepositDate()->format('mdY');
        }

        return '';
    }
}
