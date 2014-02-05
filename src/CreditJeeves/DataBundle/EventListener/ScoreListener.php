<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Enum\LeadStatus;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Entity\Score;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
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
        /** @var $score Score */
        $score = $eventArgs->getEntity();
        if (!($score instanceof Score)) {
            return;
        }

        $scoreNumber = $score->getScore();

        if ($scoreNumber > 1000) {
            $score->setScore(0);
        }

        $user = $score->getUser();

        if ($user->getType() !== UserType::APPLICANT) {
            return;
        }

        $leads = $user->getUserLeads();

        if (empty($leads)) {
            return;
        }

        /** @var $lead Lead */
        foreach ($leads as $lead) {
            if ($lead->getStatus() !== LeadStatus::ACTIVE) {
                continue;
            }

            $needScore = $lead->getTargetScore();

            if ($score->getScore() > $needScore) {
                $lead->setStatus(LeadStatus::READY);
            }

        }

        $em->flush();
    }
}
