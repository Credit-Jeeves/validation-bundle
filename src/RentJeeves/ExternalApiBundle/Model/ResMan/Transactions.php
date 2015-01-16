<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Transactions
{
    /**
     * @Serializer\SerializedName("Charge")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Charge")
     * @Serializer\Groups({"ResMan"})
     */
    protected $charge;

    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Payment")
     * @Serializer\Groups({"ResMan"})
     */
    protected $payment;

    /**
     * @Serializer\SerializedName("Credit")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Credit")
     * @Serializer\Groups({"ResMan"})
     */
    protected $credit;

    /**
     * @return Charge
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * @param Charge $charge
     */
    public function setCharge(Charge $charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return Credit
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param Credit $credit
     */
    public function setCredit(Credit $credit)
    {
        $this->credit = $credit;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }
}
