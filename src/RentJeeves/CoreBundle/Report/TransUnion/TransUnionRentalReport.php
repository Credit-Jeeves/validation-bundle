<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Annotation as Serializer;
use DateTime;

/**
 * @DI\Service("rental_report.trans_union")
 */
class TransUnionRentalReport 
{
    protected $header;
    protected $records;

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
        $this->createHeader();
        $this->records = array(new ReportRecord());
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
        $this->header = new ReportHeader();
        $lastActivityDate = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $this->header->setActivityDate(new DateTime($lastActivityDate));
    }

    protected function createRecords()
    {

    }
}
