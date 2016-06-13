<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;

class Customers
{
    /**
     * @Serializer\Type("ArrayCollection<RentJeeves\ExternalApiBundle\Model\ResMan\Customer>")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlList(inline = true, entry="Customer")
     */
    protected $customer = [];

    /**
     * @return ArrayCollection|Customer[]
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        $this->customer[] = $customer;
    }
}
