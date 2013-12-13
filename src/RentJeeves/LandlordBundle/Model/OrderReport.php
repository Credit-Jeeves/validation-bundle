<?php

namespace RentJeeves\LandlordBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("YsiTran")
 */
class OrderReport
{
    /**
     * @Serializer\SerializedName("Receipts")
     * @Serializer\XmlList(inline = false, entry="Receipt")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"xmlReport", "csvReport"})
     */
    protected $receipts = array();

    public function setReceipt($receipt)
    {
        $this->receipts = $receipt;
    }
    /**
     * @param mixed $receipts
     */
    public function addReceipt($receipts)
    {
        $this->receipts[] = $receipts;
    }

    /**
     * @return mixed
     */
    public function getReceipts()
    {
        return $this->receipts;
    }
}
