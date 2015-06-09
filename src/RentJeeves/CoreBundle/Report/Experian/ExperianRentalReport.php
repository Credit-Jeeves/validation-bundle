<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Annotation as Serializer;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     *
     * @Serializer\Exclude
     */
    protected $logger;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
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
