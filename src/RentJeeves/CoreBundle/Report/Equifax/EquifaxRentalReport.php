<?php

namespace RentJeeves\CoreBundle\Report\Equifax;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Annotation as Serializer;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Report\RentalReport;
use RentJeeves\CoreBundle\Report\RentalReportData;

abstract class EquifaxRentalReport implements RentalReport
{
    const REPORT_BUREAU = 'equifax';
    const REPORT_TYPE = 'base';

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
     * @var array
     *
     * @Serializer\Exclude
     */
    protected $propertyManagementData;

    /**
     * @var EquifaxReportHeader
     */
    protected $header;

    /**
     * @var EquifaxReportRecord[]
     */
    protected $records = [];

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param array $propertyManagementData
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, array $propertyManagementData = [])
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->propertyManagementData = $propertyManagementData;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializationType()
    {
        return 'rental_1';
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
    public function getReportFilename()
    {
        $today = new \DateTime();

        return sprintf(
            '%s-%s_renttrack-%s.txt',
            static::REPORT_BUREAU,
            static::REPORT_TYPE,
            $today->format('Ymd')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build(RentalReportData $params)
    {
        $this->createHeader();
        $this->createRecords($params);
    }

    protected function createHeader()
    {
        $lastActivityDate = $this->em->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $subCode = isset($this->propertyManagementData['equifax_subcode']) ?
            $this->propertyManagementData['equifax_subcode'] : '';
        $name = isset($this->propertyManagementData['name']) ? $this->propertyManagementData['name'] : '';
        $address = isset($this->propertyManagementData['address']) ? $this->propertyManagementData['address'] : '';
        $phoneNumber = isset($this->propertyManagementData['phone']) ? $this->propertyManagementData['phone'] : '';

        $this->header = new EquifaxReportHeader();
        $this->header->setActivityDate(new \DateTime($lastActivityDate));
        $this->header->setPropertyManagementSubCode($subCode);
        $this->header->setPropertyManagementName($name);
        $this->header->setPropertyManagementAddress($address);
        $this->header->setPropertyManagementPhone($phoneNumber);
    }

    /**
     * @param RentalReportData $params
     */
    abstract protected function createRecords(RentalReportData $params);
}
