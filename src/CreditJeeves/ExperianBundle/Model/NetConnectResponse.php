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
     * @Serializer\SerializedName("HostResponse")
     * @Serializer\Type("string")
     * @var string
     */
    protected $hostResponse;

    /**
     * @Serializer\SerializedName("ErrorMessage")
     * @Serializer\Type("string")
     * @var string
     */
    protected $errorMessage;

    /**
     * @return string
     */
    public function getCompletionCode()
    {
        return $this->completionCode;
    }

    /**
     * @param string $completionCode
     *
     * @return $this
     */
    public function setCompletionCode($completionCode)
    {
        $this->completionCode = $completionCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     *
     * @return $this
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

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

    /**
     * @return string
     */
    public function getHostResponse()
    {
        return $this->hostResponse;
    }

    /**
     * @param string $hostResponse
     *
     * @return $this
     */
    public function setHostResponse($hostResponse)
    {
        $this->hostResponse = $hostResponse;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
