<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property as YardiProperty;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionPropertyCustomer;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentTransactionUnit;

class FullResident
{
    /**
     * @var ResidentLeaseFile
     */
    protected $residentData;

    /**
     * @var ResidentsResident
     */
    protected $resident;

    /**
     * @var YardiProperty
     */
    protected $property;

    /**
     * @var ResidentTransactionUnit
     */
    protected $ResidentTransactionPropertyCustomer;

    /**
     * @return ResidentTransactionPropertyCustomer
     */
    public function getResidentTransactionPropertyCustomer()
    {
        return $this->ResidentTransactionPropertyCustomer;
    }

    /**
     * @param ResidentTransactionPropertyCustomer $ResidentTransactionPropertyCustomer
     */
    public function setResidentTransactionPropertyCustomer(
        ResidentTransactionPropertyCustomer $ResidentTransactionPropertyCustomer
    ) {
        $this->ResidentTransactionPropertyCustomer = $ResidentTransactionPropertyCustomer;
    }

    /**
     * @return YardiProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param YardiProperty $property
     */
    public function setProperty(YardiProperty $property)
    {
        $this->property = $property;
    }

    /**
     * @return ResidentLeaseFile
     */
    public function getResidentData()
    {
        return $this->residentData;
    }

    /**
     * @param ResidentLeaseFile $residentData
     */
    public function setResidentData(ResidentLeaseFile $residentData = null)
    {
        $this->residentData = $residentData;
    }

    /**
     * @return ResidentsResident
     */
    public function getResident()
    {
        return $this->resident;
    }

    /**
     * @param ResidentsResident $resident
     */
    public function setResident(ResidentsResident $resident)
    {
        $this->resident = $resident;
    }
}
