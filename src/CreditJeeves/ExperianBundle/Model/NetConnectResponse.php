<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Products;

/**
 * @Serializer\XmlRoot("NetConnectResponse")
 */
class NetConnectResponse
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Products")
     * @Serializer\SerializedName("Products")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $products;

    /**
     * @param Products $products
     */
    public function setProducts(Products $products)
    {
        $this->products = $products;
    }

    /**
     * @return Products
     */
    public function getProducts()
    {
        return $this->products;
    }
}
