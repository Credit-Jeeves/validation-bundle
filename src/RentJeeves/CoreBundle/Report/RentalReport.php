<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;

abstract class RentalReport
{
    /**
     * @Serializer\Exclude
     */
    protected $em;

    public function __construct($em, $reportMonth, $reportYear)
    {
        $this->em = $em;
        $this->createHeader();
        $this->createRecords($reportMonth, $reportYear);
    }

    abstract public function getSerializationType();

    abstract public function getReportFilename();

    abstract public function createHeader();

    abstract public function createRecords($month, $year);
}
