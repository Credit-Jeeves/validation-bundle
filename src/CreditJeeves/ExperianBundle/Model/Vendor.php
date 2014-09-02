<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Vendor")
 */
class Vendor
{
    /**
     * @Serializer\SerializedName("VendorNumber")
     * @Serializer\Groups({"PreciseID"})
     * @var string
     */
    protected $vendorNumber = 'P94';

    /**
     * @return string
     */
    public function getVendorNumber()
    {
        return $this->vendorNumber;
    }

    /**
     * @param string $vendorNumber
     */
    public function setVendorNumber($vendorNumber)
    {
        $this->vendorNumber = $vendorNumber;
    }
}
