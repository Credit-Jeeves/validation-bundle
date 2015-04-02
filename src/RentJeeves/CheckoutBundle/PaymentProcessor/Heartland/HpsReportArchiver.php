<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use RuntimeException;
use DateTime;

/**
 * @DI\Service("payment_processor.hps_report.archiver", public=false)
 */
class HpsReportArchiver
{
    /** @var string */
    protected $reportPath;

    /**
     * @DI\InjectParams({
     *     "reportPath" = @DI\Inject("%payment_processor.hps.report_path%"),
     * })
     */
    public function __construct($reportPath)
    {
        $this->reportPath = $reportPath;
    }

    /**
     * @param string $filename
     * @param string $suffix
     * @return bool
     */
    public function archive($filename, $suffix = '')
    {
        $archivePath = sprintf('%s/%s', $this->getArchiveDir(), $this->getArchiveFilename($suffix));

        try {
            $filesystem = new Filesystem();
            $filesystem->mkdir($this->getArchiveDir());
            $filesystem->rename($filename, $archivePath);
        } catch (IOException $e) {
            throw new RuntimeException(
                sprintf('An error occurred while trying to archive HPS report: %s', $e->getMessage())
            );
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getArchiveDir()
    {
        $now = new DateTime();

        return sprintf('%s/archive/%s/%s', $this->reportPath, $now->format('Y'), $now->format('m'));
    }

    /**
     * @param string $suffix
     * @return string
     */
    protected function getArchiveFilename($suffix)
    {
        $now = new DateTime();

        return sprintf('%s%s.csv', $now->format('d-H-i-s'), $suffix);
    }
}
