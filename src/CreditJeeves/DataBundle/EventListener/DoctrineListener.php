<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Tradeline;
use CreditJeeves\DataBundle\Entity\ApplicantIncentive;
use CreditJeeves\ArfBundle\Map\ArfTradelines;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class DoctrineListener
{
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Tradeline) {
            $this->checkCompleted($entity, $em);
        }
    }
}
