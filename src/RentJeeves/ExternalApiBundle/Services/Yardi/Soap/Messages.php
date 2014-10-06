<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Message;

/**
 * @Serializer\XmlRoot("Messages")
 */
class Messages
{
    /**
     * @Serializer\SerializedName("Message")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Message")
     */
    protected $message;

    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
