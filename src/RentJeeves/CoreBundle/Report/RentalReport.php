<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;

abstract class RentalReport
{
    /**
     * @Serializer\Exclude
     */
    protected $em;

    /**
     * @Serializer\Exclude
     */
    protected $params;

    public function __construct($em, $reportMonth, $reportYear, $params = array())
    {
        $this->em = $em;
        $this->params = $params;
        $this->createHeader($params);
        $this->createRecords($reportMonth, $reportYear);
    }

    abstract public function getSerializationType();

    abstract public function getReportFilename();

    abstract public function createHeader($params);

    abstract public function createRecords($month, $year);

    abstract public function isEmpty();
}
