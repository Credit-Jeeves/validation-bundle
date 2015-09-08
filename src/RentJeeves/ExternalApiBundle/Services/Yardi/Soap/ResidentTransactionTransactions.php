<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionTransactions
{
    /**
     * @Serializer\SerializedName("Charge")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionCharge")
     */
    protected $charge;

    /**
     * @param mixed $charge
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
}
