<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionTransactions
{
    /**
     * @Serializer\SerializedName("Payment")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPayment")
     */
    protected $payment;

    /**
     * @Serializer\SerializedName("Charge")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionCharge")
     */
    protected $charge;

    /**
     * @param ResidentTransactionCharge $charge
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return ResidentTransactionCharge
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * @return ResidentTransactionPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param ResidentTransactionPayment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }
}
