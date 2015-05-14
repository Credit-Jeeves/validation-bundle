<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci;

use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @DI\Service("payment_processor.aci.report_archiver", public=false)
 */
class AciReportArchiver
{
    /**
     * @var string
     */
    protected $reportPath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string $reportPath
     * @param LoggerInterface $logger
     *
     * @DI\InjectParams({
     *     "reportPath" = @DI\Inject("%aci.sftp.report_path%"),
     *     "logger" = @DI\Inject("logger")
     *  })
     */
    public function __construct($reportPath, LoggerInterface $logger)
    {
        $this->reportPath = $reportPath;
        $this->logger = $logger;
    }

    /**
     * @param string   $filePath
     */
    public function archive($filePath)
    {
        $fileExtension =  substr(strrchr($filePath, '.'), 1);

        $now = new \DateTime();
        $archiveDir = sprintf('%s/archive/%s/%s', $this->reportPath, $now->format('Y'), $now->format('m'));
        $archiveFilename = sprintf('%s/%s.%s', $archiveDir, $now->format('d-H-i-s'), $fileExtension);

        try {
            $this->logger->debug(sprintf('ACI: Trying to archive report "%s".', $filePath));
            $filesystem = new Filesystem();
            $filesystem->mkdir($archiveDir);
            $filesystem->rename($filePath, $archiveFilename);
        } catch (IOException $e) {
            $this->logger->debug(
                sprintf(
                    'ACI: Archive ACI report - FAILED : %s',
                    $e->getMessage()
                )
            );
        }
    }
}
