<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

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
    public function find($suffix = '')
    {
        $finder = new Finder();
        $finder->files()->in($this->reportPath)->name("*{$suffix}.csv")->depth('== 0');
        if ($finder->count() == 0) {
            return null;
        }

        foreach ($finder as $file) {
            return $file->getRealpath();
        }
    }

    public function findBySuffix($suffix)
    {
        return $this->find($suffix);
    }

    /**
     * @param $filename
     * @return bool
     * @throws \RuntimeException
     */
    public function archive($filename, $suffix = '')
    {
        $now = new DateTime();
        $archiveDir = sprintf('%s/archive/%s/%s', $this->reportPath, $now->format('Y'), $now->format('m'));
        $archiveFilename = sprintf('%s/%s%s.csv', $archiveDir, $now->format('d-H-i-s'), $suffix);

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
