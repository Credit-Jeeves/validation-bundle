<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\Report\RentalReport;
use RentJeeves\CoreBundle\Report\RentalReportData;

abstract class ExperianRentalReport implements RentalReport
{
    /**
     * @var array
     */
    protected $records = [];

    /**
     * @var EntityManagerInterface
     *
     * @Serializer\Exclude
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializationType()
    {
        return 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return count($this->records) == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RentalReportData $params)
    {
        $this->createRecords($params);
    }

    /**
     * @param RentalReportData $params
     * @return void
     */
    abstract protected function createRecords(RentalReportData $params);
}
