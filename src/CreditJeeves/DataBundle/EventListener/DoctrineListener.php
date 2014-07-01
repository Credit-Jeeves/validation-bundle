<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\Tradeline;
use CreditJeeves\DataBundle\Entity\ApplicantIncentive;
use CreditJeeves\ArfBundle\Map\ArfTradelines;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class DoctrineListener
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

    /**
     * @param Report $report
     *
     * @return int
     */
    protected function getReportScore($report)
    {
        return $report->getArfReport()->getScore();
    }

    protected function setScore(ReportPrequal $report, $em)
    {
        $newScore = $this->getReportScore($report);
        if ($newScore > 1000) {
            $newScore = 0;
        }
        $score = new Score();
        $score->setUser($report->getUser());
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
