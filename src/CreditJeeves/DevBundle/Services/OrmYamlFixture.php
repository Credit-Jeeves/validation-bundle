<?php

namespace CreditJeeves\DevBundle\Services;

use Khepin\YamlFixturesBundle\Fixture\OrmYamlFixture as Base;
use Doctrine\Common\Persistence\ObjectManager;

class OrmYamlFixture extends Base
{
    protected $events = array(
        array(
            'class'  =>'RentJeeves\DataBundle\EventListener\ContractListener',
            'events' => array('prePersist')
        ),
        array(
            'class'  =>'RentJeeves\DataBundle\EventListener\PaymentListener',
            'events' => array('prePersist')
        )
    );

    public function load(ObjectManager $manager, $tags = null)
    {
        $events = array('prePersist');
        foreach ($manager->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                $manager->getEventManager()->removeEventListener(
                    $this->getEvents($listener),
                    $listener
                );
            }
        }
        parent::load($manager, $tags);
    }

    protected function getEvents($lisneter)
    {
        foreach ($this->events as $event) {
            if ($lisneter instanceof $event['class']) {
                return $event['events'];
            }
        }

        return array();
    }
}
