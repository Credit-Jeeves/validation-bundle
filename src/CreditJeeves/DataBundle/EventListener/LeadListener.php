<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\CoreBundle\Mailer\Mailer;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Enum\LeadStatus;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Entity\Score;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;

/**
 * @Service("lead.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="preUpdate",
 *         "method"="preUpdate"
 *     }
 * )
 */
class LeadListener
{
    protected $container;

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * We need send email to Applicant when his Applicant change status from ACTIVE to READY
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $lead = $eventArgs->getEntity();
        $id = $lead->getId();

        if (!($lead instanceof Lead)) {
            return;
        }

        if (!$eventArgs->hasChangedField('status')) {
            return;
        }

        if ($eventArgs->getNewValue('status') !== LeadStatus::READY) {
            return;
        }

        if ($eventArgs->getOldValue('status') !== LeadStatus::ACTIVE) {
            return;
        }
        //@todo send email to dealer, will be created seperated task
        $this->container->get('project.mailer')->sendTargetApplicant($lead);
    }
}
