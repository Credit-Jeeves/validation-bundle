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
}
