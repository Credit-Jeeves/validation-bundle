<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Property
{
    /**
     * @Serializer\SerializedName("RT_Customer")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer>")
     * @Serializer\XmlList(inline = true, entry = "RT_Customer")
     * @Serializer\Groups({"ResMan"})
     */
    protected $rtCustomers;

    /**
     * @return array
     */
    public function getRtCustomers()
    {
        return $this->rtCustomers;
    }

    /**
     * @param RtCustomer $rtCustomers
     */
    public function addRtCustomer(RtCustomer $customer)
    {
        $this->rtCustomers[] = $customer;
    }
}
