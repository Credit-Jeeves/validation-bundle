<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\Tradeline;
use CreditJeeves\DataBundle\Entity\ApplicantIncentive;
use CreditJeeves\ArfBundle\Map\ArfTradelines;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("data.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes={
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes={
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 */
class Doctrine
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof ReportPrequal) {
            $this->setScore($entity, $em);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Tradeline) {
            $this->checkCompleted($entity, $em);
        }
        
    }

    private function setScore(ReportPrequal $Report, $em)
    {
        $arfReport = $Report->getArfReport();
        $newScore = $arfReport->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE);
        if ($newScore > 1000) {
            $newScore = 0;
        }
            $score = new Score();
            $score->setUser($Report->getUser());
            $score->setScore($newScore);
            $em->persist($score);
    }

    private function checkCompleted(Tradeline $tradeline, $em)
    {
        $isCompleted = $tradeline->getIsCompleted();
        if ($isCompleted) {
            $incentive = new ApplicantIncentive();
            $incentive->createIncentive($tradeline, $em);
        }
    }
}
