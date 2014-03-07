<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;

/**
 * @DI\Service("rental_report.trans_union")
 */
class TransUnionRentalReport 
{
    protected $header;
//    protected $records;

    /**
     * @Serializer\Exclude
     */
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    // Move to lazy constructor?
    public function getReport()
    {
        $this->createHeader();
        $this->createRecords();
    }

    // Remove?
    public function getHeader()
    {
        return $this->header;
    }

    // Remove?
    public function getRecords()
    {
        return $this->records;
    }

    protected function createHeader()
    {
        $header = new ReportHeader();
    }

    protected function createRecords()
    {

    }
}
