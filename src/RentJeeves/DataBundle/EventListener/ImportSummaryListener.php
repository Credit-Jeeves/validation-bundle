<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Monolog\Logger;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\DataBundle\Model\ImportSummary;

class ImportSummaryListener
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var Skip32IdEncoder
     */
    public $encoder;

    /**
     * @param Skip32IdEncoder $encoder
     */
    public function __construct(Skip32IdEncoder $encoder)
    {
        $this->logger = new Logger(get_class());
        $this->encoder = $encoder;
    }

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
     * @throws \RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException
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

        $publicId = $this->encoder->encode($importSummary->getId());
        $importSummary->setPublicId($publicId);
        $eventArgs->getObjectManager()->flush($importSummary);
    }
}
