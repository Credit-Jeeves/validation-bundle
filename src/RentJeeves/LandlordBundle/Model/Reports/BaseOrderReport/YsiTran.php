<?php

namespace RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("YsiTran")
 */
class YsiTran
{
    /**
     * @Serializer\XmlList(inline = false, entry="Receipt")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"xmlBaseReport"})
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
