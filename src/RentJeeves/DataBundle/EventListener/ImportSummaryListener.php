<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Model\ImportSummary;

class ImportSummaryListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->setupPublicId($eventArgs);
    }

    /**
     * Setup public ID for summary import
     *
     * @param  LifecycleEventArgs                                                 $eventArgs
     */
    public function setupPublicId(LifecycleEventArgs $eventArgs)
    {
        $importSummary = $eventArgs->getEntity();
        if (!$importSummary instanceof ImportSummary) {
            return;
        }

        $publicId = $importSummary->getPublicId();

        if (!empty($publicId)) {
            return;
        }

        $importSummary->setPublicId(uniqid());
        $eventArgs->getObjectManager()->flush($importSummary);
    }
}
