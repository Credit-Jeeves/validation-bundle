<?php

namespace RentJeeves\ApiBundle\Forms\DataTransformer;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class StreetTransformerListener implements EventSubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event)
    {
        $submittedData = $event->getData();
        if (isset($submittedData['street'])) {
            if (preg_match('/^([1-9][^\s]*)\s(.+)$/s', $submittedData['street'], $array)) {
                $submittedData['number'] = $array[1];
                $submittedData['street'] = $array[2];
            }
        }

        $event->setData($submittedData);
    }
}
