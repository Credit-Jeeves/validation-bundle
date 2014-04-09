<?php

namespace RentJeeves\CoreBundle\Report;

use DateTime;

class TransUnionRentalReport extends RentalReport
{
    protected $header;
    protected $records;

    public function getSerializationType()
    {
        return 'trans_union_rental';
    }

    public function getReportFilename()
    {
        $today = new DateTime();

        return sprintf('renttrack-%s.txt', $today->format('Ymd'));
    }

    public function createHeader($params)
    {
        $lastActivityDate = $this->em->getRepository('RjDataBundle:Contract')->getLastActivityDate();
        $propertyManagementName = isset($params['name'])? $params['name'] : '';
        $propertyManagementAddress = isset($params['address'])? $params['address'] : '';
        $propertyManagementPhoneNumber = isset($params['phone'])? $params['phone'] : '';

        $this->header = new TransUnionReportHeader($params);
        $this->header->setActivityDate(new DateTime($lastActivityDate));
        $this->header->setPropertyManagementName($propertyManagementName);
        $this->header->setPropertyManagementAddress($propertyManagementAddress);
        $this->header->setPropertyManagementPhone($propertyManagementPhoneNumber);
    }

    public function createRecords($month, $year)
    {
        $this->records = array();
        $contracts = $this->em->getRepository('RjDataBundle:Contract')
            ->getContractsForTransUnionRentalReport($month, $year);

        $operationRepo = $this->em->getRepository('DataBundle:Operation');

        foreach ($contracts as $contract) {
            $rentOperation = $operationRepo->getRentOperationForMonth($contract->getId(), $month, $year);
            $this->records[] = new TransUnionReportRecord($contract, $month, $year, $rentOperation);
        }
    }
}
