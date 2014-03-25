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

    abstract function getSerializationType();
    abstract function getReportFilename();
    abstract function createHeader();
    abstract function createRecords($reportMonth, $reportYear);
} 
