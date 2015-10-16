<?php

namespace RentJeeves\ComponentBundle\CreditSummaryReport;

use RentJeeves\DataBundle\Enum\CreditSummaryVendor;

class CreditSummaryReportVendorFactory
{
    /**
     * @var TransunionReportBuilder
     */
    protected $transunionReportBuilder;

    /**
     * @var ExperianReportBuilder
     */
    protected $experianReportBuilder;

    /**
     * @var string
     */
    protected $defaultVendor;

    /**
     * @param TransunionReportBuilder $transunionReportBuilder
     * @param ExperianReportBuilder $experianReportBuilder
     * @param string $defaultVendor
     */
    public function __construct(
        TransunionReportBuilder $transunionReportBuilder,
        ExperianReportBuilder $experianReportBuilder,
        $defaultVendor
    ) {
        $this->transunionReportBuilder = $transunionReportBuilder;
        $this->experianReportBuilder = $experianReportBuilder;
        $this->defaultVendor = $defaultVendor;
    }

    /**
     * @param $vendor
     * @return CreditSummaryReportBuilderInterface
     * @throws \Exception
     */
    public function getReportBuilder($vendor = null)
    {
        if (is_null($vendor)) {
            $vendor = $this->defaultVendor;
        }
        switch ($vendor) {
            case CreditSummaryVendor::EXPERIAN:
                return $this->experianReportBuilder;
            case CreditSummaryVendor::TRANSUNION:
                return $this->transunionReportBuilder;
            default:
                throw new \Exception(sprintf('Unknown credit summary vendor \'%s\'', $vendor));
        }
    }
}
