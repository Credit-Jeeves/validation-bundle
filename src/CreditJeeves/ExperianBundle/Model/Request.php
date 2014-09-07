<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Request")
 */
class Request
{
    /**
     * @Serializer\XmlAttributeMap
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $namespace = array(
        'xmlns' => 'http://www.experian.com/WebDelivery'
    );

    /**
     * @Serializer\XmlAttributeMap
     * @Serializer\Groups({"PreciseID", "PreciseIDQuestions", "CreditProfile"})
     * @var string
     */
    protected $attributes = array(
        'version' => '1.0'
    );

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Products")
     * @Serializer\Groups({"PreciseID", "PreciseIDQuestions", "CreditProfile"})
     * @var Products
     */
    protected $products;

    /**
     * @return Products
     */
    public function getProducts()
    {
        if (null == $this->products) {
            $this->products = new Products();
        }
        return $this->products;
    }

    /**
     * @param Products $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }
}
