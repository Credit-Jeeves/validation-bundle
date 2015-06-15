<?php

namespace RentJeeves\CoreBundle\Report;

interface RentalReport
{
    /**
     * Returns the type of serialization.
     *
     * @return string
     */
    public function getSerializationType();

    /**
     * Returns a new report filename.
     *
     * @return string
     */
    public function getReportFilename();

    /**
     * Builds report records.
     *
     * @param RentalReportData $data
     * @return void
     */
    public function build(RentalReportData $data);

    /**
     * Returns if report is empty.
     *
     * @return boolean
     */
    public function isEmpty();
}
