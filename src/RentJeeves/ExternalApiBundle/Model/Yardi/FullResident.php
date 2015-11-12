<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;

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
