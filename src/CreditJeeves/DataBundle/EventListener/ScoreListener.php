<?php
namespace CreditJeeves\DataBundle\EventListener;

use RentJeeves\DataBundle\Entity\Score;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @Service("score.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist" 
 *     }
 * )
 */
class ScoreListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Score) {
            $score = $entity->getScore();
            if ($score > 1000) {
                $entity->setScore(0);
            }
        }
    }
}
