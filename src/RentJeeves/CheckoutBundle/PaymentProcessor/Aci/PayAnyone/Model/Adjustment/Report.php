<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ADJUSTMENT_FILE")
 */
class Report
{
    /**
     * @Serializer\Type("DateTime<'ymd'>")
     * @Serializer\SerializedName("REPORT_DATE")
     */
    protected $depositDate;

    /**
     * @Serializer\Type("RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Originator")
     * @Serializer\SerializedName("ORIGINATOR")
     */
    protected $originator;

    /**
     * @return mixed
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * @param mixed $depositDate
     */
    public function setDepositDate($depositDate)
    {
        $this->depositDate = $depositDate;
    }

    /**
     * @return mixed
     */
    public function getOriginator()
    {
        return $this->originator;
    }

    /**
     * @param mixed $originator
     */
    public function setOriginator($originator)
    {
        $this->originator = $originator;
    }
}
