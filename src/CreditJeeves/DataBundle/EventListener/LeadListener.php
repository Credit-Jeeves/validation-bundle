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
        $lead = $eventArgs->getEntity();

        if (!($lead instanceof Lead)) {
            return;
        }
        $em = $eventArgs->getEntityManager();
        $id = $lead->getId();

        if ($eventArgs->hasChangedField('status')
            &&
            $eventArgs->getNewValue('status') === LeadStatus::READY
            &&
            $eventArgs->getOldValue('status') === LeadStatus::ACTIVE
        ) {
            //@todo send email to dealer, will be created seperated task
            $this->container->get('project.mailer')->sendTargetApplicant($lead);
        }
    }
}
