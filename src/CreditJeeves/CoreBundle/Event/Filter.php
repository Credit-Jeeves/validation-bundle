<?php
namespace CreditJeeves\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class Filter extends Event
{
    protected $responseEvent;

    public function setResponseEvent(GetResponseEvent $responseEvent)
    {
        $this->responseEvent = $responseEvent;
    }

    /**
     * @return GetResponseEvent
     */
    public function getResponseEvent()
    {
        return $this->responseEvent;
    }
}
