<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("data.event_listener.doctrine")
 * @Tag("doctrine.event_listener", attributes = { "event" = "prePersist", "method" = "prePersist" })
 * @Tag("doctrine.event_listener", attributes = { "event" = "onFlush", "method" = "onFlush" })
 */
class Doctrine
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof ReportPrequal) {
            $arfReport = $entity->getArfReport();
            $newScore = $arfReport->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE);
            $score = new Score();
            $score->setUser($entity->getUser());
            $score->setScore($newScore);
            $em->persist($score);
        }
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
    }
}
