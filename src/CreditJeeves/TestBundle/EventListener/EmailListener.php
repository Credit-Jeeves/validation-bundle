<?php
namespace CreditJeeves\TestBundle\EventListener;

use \Swift_Message;
use \Swift_Events_SendEvent;
use \Swift_Events_SendListener;

class EmailListener implements Swift_Events_SendListener
{
    /**
     * @var array
     */
    protected $preSendMessages = array();

    /**
     * @var array
     */
    protected $postSendMessages = array();

    public function clean()
    {
        $this->preSendMessages = array();
        $this->postSendMessages = array();
    }

    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->preSendMessages[] = $evt->getMessage();
    }

    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->postSendMessages[] = $evt->getMessage();
    }

    public function getPreSendMessages()
    {
        return $this->preSendMessages;
    }

    public function getPostSendMessages()
    {
        return $this->postSensMessages;
    }

    /**
     * @param $item
     *
     * @return Swift_Message
     */
    public function getPreSendMessage($item)
    {
        if (empty($this->preSendMessages[$item])) {
            return null;
        }
        return $this->preSendMessages[$item];
    }

    /**
     * @param $item
     *
     * @return Swift_Message
     */
    public function getPostSendMessage($item)
    {
        if (empty($this->postSensMessages[$item])) {
            return null;
        }
        return $this->postSensMessages[$item];
    }
}
