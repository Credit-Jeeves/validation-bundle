<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class ResMan
{

    /**
     * @Serializer\SerializedName("MethodName")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $methodName;

    /**
     * @Serializer\SerializedName("Status")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $status;

    /**
     * @Serializer\SerializedName("AccountID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $accountId;

    /**
     * @Serializer\SerializedName("PropertyID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $propertyId;

    /**
     * @Serializer\SerializedName("Response")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $response;

    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param string $accountId
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    /**
     * @param string $propertyId
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
