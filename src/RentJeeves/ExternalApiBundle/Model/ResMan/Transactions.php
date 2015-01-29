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
     * @Serializer\SerializedName("Concession")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Concession")
     * @Serializer\Groups({"ResMan"})
     */
    protected $concession;

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
     * @return Concession
     */
    public function getConcession()
    {
        return $this->concession;
    }

    /**
     * @param Concession $credit
     */
    public function setConcession(Concession $credit)
    {
        $this->concession = $credit;
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
