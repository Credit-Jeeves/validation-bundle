<?php

namespace RentJeeves\LandlordBundle\Model\Reports\BaseOrderReport;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("YsiTran")
 */
class YsiTran
{
    /** @Serializer\XmlAttributeMap */
    private $attributes = array(
        'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance"
    );

    /**
     * @Serializer\XmlList(inline = false, entry="Receipt")
     * @Serializer\XmlKeyValuePairs
     */
    protected $receipts = array();

    /**
     * @param mixed $receipts
     */
    public function addReceipt(Receipt $receipts)
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
