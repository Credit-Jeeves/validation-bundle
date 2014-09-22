<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\PreciseIDServer;

/**
 * @Serializer\XmlRoot("Messages")
 */
class Messages
{
    /**
     * @Serializer\SerializedName("Message")
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Message")
     * @var Message
     */
    protected $message;

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Message $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
