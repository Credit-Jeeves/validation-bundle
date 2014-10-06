<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;


use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;

trait ImportResident
{
    /**
     * @var array
     */
    protected $usedResidentsIds = array();

    /**
     * @param string $residentId
     */
    protected function addResidentId($residentId)
    {
        if (!isset($this->usedResidentsIds[$residentId])) {
            $this->usedResidentsIds[$residentId] = 1;
        } else {
            $this->usedResidentsIds[$residentId]++;
        }
    }

    protected function clearResidentIds()
    {
        $this->usedResidentsIds = array();
    }

    /**
     * @param ResidentMapping $residentMapping
     * @return bool
     */
    public function isUsedResidentId(ResidentMapping $residentMapping)
    {
        $id = $residentMapping->getResidentId();
        return (isset($this->usedResidentsIds[$id]) && $this->usedResidentsIds[$id] > 1)? true : false;
    }

    /**
     * @param Tenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    protected function createResident(Tenant $tenant, array $row)
    {
        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($this->user->getHolding());
        $residentMapping->setResidentId($row[ImportMapping::KEY_RESIDENT_ID]);
        $this->addResidentId($row[ImportMapping::KEY_RESIDENT_ID]);

        return $residentMapping;
    }

    /**
     * @param Tenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    public function getResident(Tenant $tenant, array $row)
    {
        if (is_null($tenant->getId())) {
            return $this->createResident($tenant, $row);
        }

        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'tenant'        => $tenant->getId(),
                'holding'       => $this->user->getHolding()->getId(),
                'residentId'    => $row[ImportMapping::KEY_RESIDENT_ID],
            )
        );

        if (empty($residentMapping)) {
            return $this->createResident($tenant, $row);
        }

        return $residentMapping;
    }
}
