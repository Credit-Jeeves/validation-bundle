<?php

namespace RentJeeves\CheckoutBundle\Payment;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use RuntimeException;
use DateTime;

/**
 * @DI\Service("payment.report.finder")
 */
class HpsReportFinder 
{
    protected $reportPath;

    /**
     * @DI\InjectParams({
     *     "reportPath" = @DI\Inject("%payment_report_path%"),
     * })
     */
    public function __construct($reportPath)
    {
        $this->reportPath = $reportPath;
    }

    /**
     * @return null|string
     */
    public function find()
    {
        $finder = new Finder();
        $finder->files()->in($this->reportPath)->name('*.csv')->depth('== 0');
        if ($finder->count() == 0) {
            return null;
        }

        foreach ($finder as $file) {
            return $file->getRealpath();
        }
    }

    /**
     * @param $filename
     * @return bool
     * @throws \RuntimeException
     */
    public function archive($filename)
    {
        $now = new DateTime();
        $archiveDir = sprintf('%s/archive/%s/%s', $this->reportPath, $now->format('Y'), $now->format('m'));
        $archiveFilename = sprintf('%s/%s.csv', $archiveDir, $now->format('d-H-i-s'));

        try {
            $filesystem = new Filesystem();
            $filesystem->mkdir($archiveDir);
            $filesystem->rename($filename, $archiveFilename);
        } catch (IOException $e) {
            throw new RuntimeException(
                sprintf('An error occurred while trying to archive HPS report: %s', $e->getMessage())
            );
        }

        return true;
    }
} 
