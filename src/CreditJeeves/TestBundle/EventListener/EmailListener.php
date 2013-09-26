<?php
namespace CreditJeeves\TestBundle\EventListener;

class EmailListener implements \Swift_Events_SendListener
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

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $this->preSendMessages[] = $evt->getMessage();
    }

    public function sendPerformed(\Swift_Events_SendEvent $evt)
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
}
