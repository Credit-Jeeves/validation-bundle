<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @DI\Service("payment_processor.hps_report.finder")
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
     * Finds all report files with a given suffix.
     *
     * @param string $suffix
     * @return array
     */
    public function find($suffix = '')
    {
        $finder = new Finder();
        $finder->files()->in($this->reportPath)->name("*{$suffix}.csv")->depth('== 0');
        if ($finder->count() == 0) {
            return [];
        }

        $foundFiles = [];
        foreach ($finder as $file) {
            $foundFiles[] = $file->getRealpath();
        }

        return $foundFiles;
    }

    /**
     * @param $suffix
     * @return array
     */
    public function findBySuffix($suffix)
    {
        return $this->find($suffix);
    }
}
