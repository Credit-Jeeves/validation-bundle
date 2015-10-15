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
     * @param string $defaultVendor
     */
    public function setDefaultVendor($defaultVendor)
    {
        $this->defaultVendor = $defaultVendor;
    }

    /**
     * @param TransunionReportBuilder $transunionReportBuilder
     * @param ExperianReportBuilder $experianReportBuilder
     */
    public function setReportBuilders(
        TransunionReportBuilder $transunionReportBuilder,
        ExperianReportBuilder $experianReportBuilder
    ) {
        $this->transunionReportBuilder = $transunionReportBuilder;
        $this->experianReportBuilder = $experianReportBuilder;
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
