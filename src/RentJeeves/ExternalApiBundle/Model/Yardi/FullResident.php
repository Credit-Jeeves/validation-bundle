<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property as YardiProperty;

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
    public function setResidentData(ResidentLeaseFile $residentData)
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
