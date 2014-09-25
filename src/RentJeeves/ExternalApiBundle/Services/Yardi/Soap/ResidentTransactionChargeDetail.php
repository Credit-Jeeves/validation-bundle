<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentTransactionChargeDetail
{
    /**
     * @Serializer\SerializedName("BalanceDue")
     * @Serializer\Type("double")
     */
    protected $balanceDue;

    /**
     * @param mixed $balanceDue
     */
    public function setBalanceDue($balanceDue)
    {
        $this->balanceDue = $balanceDue;
    }

    /**
     * @return mixed
     */
    public function getBalanceDue()
    {
        return $this->balanceDue;
    }
}
