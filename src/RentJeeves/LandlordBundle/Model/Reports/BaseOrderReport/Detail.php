<?php

namespace RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Detail")
 */
class Detail
{
    protected $amount;

    protected $notes;

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
