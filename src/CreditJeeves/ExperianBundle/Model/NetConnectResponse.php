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
     * @Serializer\SerializedName("CompletionCode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $completionCode;

    /**
     * @Serializer\SerializedName("ReferenceId")
     * @Serializer\Type("string")
     * @var string
     */
    protected $referenceId;

    /**
     * @Serializer\SerializedName("Products")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Products")
     * @Serializer\Groups({"CreditJeeves"})
     *
     * @var Products
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
