<?php

namespace RentJeeves\TrustedLandlordBundle\Model\Jira;

use JMS\Serializer\Annotation as Serializer;

class Transition
{
    /**
     * @Serializer\SerializedName("to_status")
     * @Serializer\Type("string")
     * @var string
     */
    protected $toStatus;

    /**
     * @return string
     */
    public function getToStatus()
    {
        return $this->toStatus;
    }

    /**
     * @param string $toStatus
     */
    public function setToStatus($toStatus)
    {
        $this->toStatus = $toStatus;
    }
}
