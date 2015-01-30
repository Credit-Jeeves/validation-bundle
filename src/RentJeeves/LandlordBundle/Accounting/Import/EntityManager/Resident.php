<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;


use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant as EntityTenant;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property Import currentImportModel
 */
trait Resident
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
     * @return bool
     */
    public function isUsedResidentId()
    {
        $residentMapping = $this->currentImportModel->getResidentMapping();
        $id = $residentMapping->getResidentId();

        return (isset($this->usedResidentsIds[$id]) && $this->usedResidentsIds[$id] > 1)? true : false;
    }

    /**
     * @param array $row
     *
     */
    public function setResident(array $row)
    {
        if (is_null($this->currentImportModel->getTenant()->getId())) {
            $residentMapping = new ResidentMapping();
            $residentMapping->setTenant($this->currentImportModel->getTenant());
            $residentMapping->setHolding($this->user->getHolding());
            $residentMapping->setResidentId($row[Mapping::KEY_RESIDENT_ID]);
            $this->addResidentId($row[Mapping::KEY_RESIDENT_ID]);
            $this->currentImportModel->setResidentMapping($residentMapping);

            return;
        }

        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'tenant'        => $this->currentImportModel->getTenant()->getId(),
                'holding'       => $this->user->getHolding()->getId(),
                'residentId'    => $row[Mapping::KEY_RESIDENT_ID],
            )
        );

        if (empty($residentMapping)) {
            $residentMapping = new ResidentMapping();
            $residentMapping->setTenant($this->currentImportModel->getTenant());
            $residentMapping->setHolding($this->user->getHolding());
            $residentMapping->setResidentId($row[Mapping::KEY_RESIDENT_ID]);
            $this->addResidentId($row[Mapping::KEY_RESIDENT_ID]);
        }

        $this->currentImportModel->setResidentMapping($residentMapping);
    }
}
