<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Message")
 */
class Message
{
    /**
     * @Serializer\SerializedName("messageType")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    private $messageType;

    /**
     * @Serializer\Type("string")
     * @Serializer\XmlValue
     */
    protected $message;

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $messageType
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }

    /**
     * @return mixed
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

}
