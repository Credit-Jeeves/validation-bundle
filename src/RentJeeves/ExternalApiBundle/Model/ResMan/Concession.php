<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Concession
{
    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Detail")
     * @Serializer\Groups({"ResMan"})
     */
    protected $detail;

    /**
     * @return Detail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param Detail $detail
     */
    public function setDetail(Detail $detail)
    {
        $this->detail = $detail;
    }
}
