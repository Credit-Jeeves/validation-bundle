<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;


use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;

trait Resident
{
    /**
     * @var array
     */
    protected $usedResidentsIds = [];

    /**
     * @var array
     */
    protected $usedEmails = [];

    /**
     * @param string $residentId
     */
    protected function addResidentId($residentId, $email)
    {
        if (!isset($this->usedResidentsIds[$residentId])) {
            $this->usedResidentsIds[$residentId] = 1;
        }

        if (!empty($email) && !isset($this->usedEmails[$email])) {
            $this->usedEmails[$email] = $residentId;
        }

        if (!empty($email) && $residentId !== $this->usedEmails[$email]) {
            $this->usedResidentsIds[$residentId]++;
            $this->usedEmails[$email] = $residentId;
        }
    }

    protected function getEmailByResident($residentId)
    {
        return array_search($residentId, $this->usedEmails);
    }

    protected function clearResidentIds()
    {
        $this->usedResidentsIds = [];
        $this->usedEmails = [];
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
     * @param EntityTenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    protected function createResident(EntityTenant $tenant, array $row)
    {
        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($tenant);
        $residentMapping->setHolding($this->user->getHolding());
        $residentMapping->setResidentId($row[Mapping::KEY_RESIDENT_ID]);
        $this->addResidentId($row[Mapping::KEY_RESIDENT_ID], $tenant->getEmail());

        return $residentMapping;
    }

    /**
     * @param EntityTenant $tenant
     * @param array $row
     *
     * @return ResidentMapping
     */
    public function getResident(EntityTenant $tenant, array $row)
    {
        if (is_null($tenant->getId())) {
            return $this->createResident($tenant, $row);
        }
        /** @var ResidentMapping $residentMapping */
        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'tenant'        => $tenant->getId(),
                'holding'       => $this->user->getHolding()->getId(),
                'residentId'    => $row[Mapping::KEY_RESIDENT_ID],
            )
        );

        if (empty($residentMapping)) {
            return $this->createResident($tenant, $row);
        }

        $this->addResidentId($residentMapping->getResidentId(), $tenant->getEmail());
        return $residentMapping;
    }
}
